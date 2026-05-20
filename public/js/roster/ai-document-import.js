var csrfToken = $('meta[name="csrf-token"]').attr('content');
$.ajaxSetup({ headers: {'X-CSRF-TOKEN': csrfToken} });

function esc(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

var categoryLabels = {
    care_history: 'Care History',
    medications: 'Medications',
    risk_assessments: 'Risk Assessments',
    client_profile: 'Client Profile Updates',
    body_map: 'Body Map',
    dols: 'DoLS'
};

var categoryIcons = {
    care_history: 'fa-history',
    medications: 'fa-pills',
    risk_assessments: 'fa-exclamation-triangle',
    client_profile: 'fa-user',
    body_map: 'fa-body',
    dols: 'fa-shield-alt'
};

function openImportModal(clientId) {
    $('#docImportClientId').val(clientId);
    $('#docImportId').val('');
    $('#importStep1').show();
    $('#importStep2').hide();
    $('#importStep3').hide();
    $('#importError').hide();
    $('#uploadProgress').hide();
    $('#selectedFileInfo').hide();
    $('#uploadBtn').prop('disabled', true);
    $('#pdfFileInput').val('');
    $('#aiDocumentImportModal').modal('show');
}

// Drag and drop
$(document).ready(function() {
    var dropZone = $('#dropZone');

    dropZone.on('click', function() {
        $('#pdfFileInput').click();
    });

    $('#pdfFileInput').on('click', function(e) {
        e.stopPropagation();
    });

    dropZone.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css('background', '#e8f0fe');
    });

    dropZone.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css('background', '#f9f9f9');
    });

    dropZone.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css('background', '#f9f9f9');
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });

    $('#pdfFileInput').on('change', function() {
        if (this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });

    // Load documents when Documents tab is activated
    $(document).on('click', '[data-tab="clientDocumentsTab"]', function() {
        var clientId = $('#docImportClientId').val() || $('input[name="service_user_id"]').val();
        if (!clientId) {
            var urlParts = window.location.pathname.split('/');
            clientId = urlParts[urlParts.length - 1];
        }
        if (clientId) {
            loadDocumentList(clientId);
            loadImportHistory(clientId);
        }
    });

    // Restore tab state on modal close
    $('#aiDocumentImportModal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        if ($('#clientDocumentsTab').length) {
            $('#clientDocumentsTab').addClass('active');
            $('[data-tab="clientDocumentsTab"]').addClass('active');
        }
    });
});

function handleFileSelect(file) {
    $('#importError').hide();

    var allowedTypes = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword',
        'application/octet-stream',
        'application/zip',
        'application/x-zip-compressed'
    ];
    var fileExt = file.name.split('.').pop().toLowerCase();
    var allowedExts = ['pdf', 'docx', 'doc'];
    if (allowedTypes.indexOf(file.type) === -1 && allowedExts.indexOf(fileExt) === -1) {
        showImportError('Please select a PDF or Word document (.pdf, .docx).');
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        showImportError('File is too large. Maximum size is 10MB.');
        return;
    }

    $('#selectedFileName').text(esc(file.name));
    $('#selectedFileSize').text('(' + formatFileSize(file.size) + ')');
    $('#selectedFileInfo').show();
    $('#uploadBtn').prop('disabled', false);

    // Store reference to file
    $('#pdfFileInput')[0]._selectedFile = file;
}

function clearFileSelection() {
    $('#pdfFileInput').val('');
    $('#pdfFileInput')[0]._selectedFile = null;
    $('#selectedFileInfo').hide();
    $('#uploadBtn').prop('disabled', true);
}

function uploadAndExtractText() {
    var clientId = $('#docImportClientId').val();
    var fileInput = $('#pdfFileInput')[0];
    var file = fileInput._selectedFile || (fileInput.files.length > 0 ? fileInput.files[0] : null);

    if (!file) {
        showImportError('Please select a file first.');
        return;
    }

    var formData = new FormData();
    formData.append('client_id', clientId);
    formData.append('file', file);

    $('#uploadBtn').prop('disabled', true);
    $('#uploadProgress').show();
    $('#uploadStatusText').text('Uploading and extracting text from document...');
    $('#importError').hide();

    $.ajax({
        url: docImportBaseUrl + '/upload',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status) {
                $('#docImportId').val(response.import_id);
                $('#uploadStatusText').text('Analysing document with AI... This may take 10-20 seconds.');
                analyseWithAI(response.import_id);
            } else {
                showImportError(response.error || 'Upload failed.');
                resetUploadUI();
            }
        },
        error: function(xhr) {
            var msg = 'Upload failed.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                msg = xhr.responseJSON.error;
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            showImportError(msg);
            resetUploadUI();
        }
    });
}

function analyseWithAI(importId) {
    $.ajax({
        url: docImportBaseUrl + '/extract',
        type: 'POST',
        data: { import_id: importId },
        success: function(response) {
            if (response.status && response.extracted_data) {
                renderExtractedData(response.extracted_data);
                $('#importStep1').hide();
                $('#importStep2').show();
                $('#uploadProgress').hide();
            } else {
                showImportError(response.error || 'AI extraction returned no data.');
                resetUploadUI();
            }
        },
        error: function(xhr) {
            var msg = 'AI analysis failed.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                msg = xhr.responseJSON.error;
            }
            showImportError(msg);
            resetUploadUI();
        }
    });
}

function renderExtractedData(data) {
    var container = $('#extractedDataContainer');
    container.empty();

    var categories = ['care_history', 'medications', 'risk_assessments', 'client_profile', 'body_map', 'dols'];
    var hasData = false;

    categories.forEach(function(cat) {
        var items = data[cat];
        var isEmpty = !items || (Array.isArray(items) && items.length === 0) ||
                      (typeof items === 'object' && !Array.isArray(items) && Object.keys(items).length === 0);

        if (isEmpty) return;

        hasData = true;
        var count = Array.isArray(items) ? items.length : Object.keys(items).length;
        var countLabel = cat === 'client_profile' ? count + ' fields' : count + ' found';
        var icon = categoryIcons[cat] || 'fa-file';
        var label = categoryLabels[cat] || cat;

        var html = '<div class="bBorderCard mt-3 import-category-card" data-category="' + cat + '">';
        html += '<div class="d-flex justify-content-between align-items-center">';
        html += '<div class="d-flex align-items-center gap-3">';
        html += '<input type="checkbox" class="import-category-check" data-category="' + cat + '" checked style="width:18px;height:18px;">';
        html += '<i class="fa ' + esc(icon) + '" style="color:#2563eb;"></i>';
        html += '<strong>' + esc(label) + '</strong>';
        html += '<span class="careBadg">' + esc(countLabel) + '</span>';
        html += '</div>';
        html += '<button class="btn btn-sm btn-link" onclick="toggleCategoryDetail(\'' + cat + '\')">';
        html += '<i class="fa fa-chevron-down" id="chevron-' + cat + '"></i>';
        html += '</button>';
        html += '</div>';

        html += '<div class="category-detail mt-2" id="detail-' + cat + '" style="display:none;">';
        html += renderCategoryItems(cat, items);
        html += '</div>';
        html += '</div>';

        container.append(html);
    });

    if (data.document_summary) {
        container.prepend(
            '<div class="purpleBox mb-3"><div class="d-flex gap-3"><i class="bx bx-info-circle"></i>' +
            '<p class="mb-0">' + esc(data.document_summary) + '</p></div></div>'
        );
    }

    if (!hasData) {
        $('#noDataMessage').show();
        $('#confirmImportBtn').prop('disabled', true);
    } else {
        $('#noDataMessage').hide();
        updateSelectedCount();
    }

    container.on('change', '.import-category-check', updateSelectedCount);
}

function renderCategoryItems(category, items) {
    var html = '<ul class="list-unstyled mb-0" style="padding-left:40px;">';

    if (category === 'care_history') {
        items.forEach(function(item) {
            html += '<li class="mb-2"><strong>' + esc(item.title || 'Untitled') + '</strong>';
            if (item.date) html += ' &mdash; ' + esc(item.date);
            if (item.description) html += '<br><small class="text-muted">' + esc(item.description.substring(0, 200)) + '</small>';
            html += '</li>';
        });
    } else if (category === 'medications') {
        items.forEach(function(item) {
            html += '<li class="mb-2"><strong>' + esc(item.medication_name || 'Unknown') + '</strong>';
            if (item.dosage) html += ' ' + esc(item.dosage);
            if (item.frequency) html += ' &mdash; ' + esc(item.frequency);
            if (item.route) html += ' &mdash; ' + esc(item.route);
            if (item.reason_for_medication) html += '<br><small class="text-muted">Reason: ' + esc(item.reason_for_medication) + '</small>';
            html += '</li>';
        });
    } else if (category === 'risk_assessments') {
        items.forEach(function(item) {
            var levelClass = item.risk_level === 'high' ? 'color:#e74c3c' : (item.risk_level === 'medium' ? 'color:#f39c12' : 'color:#27ae60');
            html += '<li class="mb-2"><strong>' + esc(item.risk_type || 'Unknown') + '</strong>';
            if (item.risk_level) html += ' &mdash; <span style="' + levelClass + '">' + esc(item.risk_level.toUpperCase()) + '</span>';
            if (item.description) html += '<br><small class="text-muted">' + esc(item.description.substring(0, 200)) + '</small>';
            html += '</li>';
        });
    } else if (category === 'client_profile') {
        var fieldLabels = {
            allergies: 'Allergies',
            medical_notes: 'Medical Notes',
            care_needs: 'Care Needs',
            mental_health_issues: 'Mental Health',
            drug_n_alcohol_issues: 'Drug & Alcohol',
            suMobility: 'Mobility'
        };
        Object.keys(items).forEach(function(key) {
            if (items[key] && fieldLabels[key]) {
                html += '<li class="mb-2"><strong>' + esc(fieldLabels[key]) + ':</strong> ' + esc(String(items[key]).substring(0, 200)) + '</li>';
            }
        });
    } else if (category === 'body_map') {
        items.forEach(function(item) {
            html += '<li class="mb-2"><strong>' + esc(item.injury_type || 'Injury') + '</strong>';
            if (item.body_part) html += ' &mdash; ' + esc(item.body_part);
            if (item.injury_description) html += '<br><small class="text-muted">' + esc(item.injury_description.substring(0, 200)) + '</small>';
            html += '</li>';
        });
    } else if (category === 'dols') {
        items.forEach(function(item) {
            html += '<li class="mb-2"><strong>Status:</strong> ' + esc(item.dols_status || 'Unknown');
            if (item.authorisation_type) html += ' &mdash; ' + esc(item.authorisation_type);
            if (item.reason_for_dols) html += '<br><small class="text-muted">' + esc(item.reason_for_dols) + '</small>';
            html += '</li>';
        });
    }

    html += '</ul>';
    return html;
}

function toggleCategoryDetail(category) {
    var detail = $('#detail-' + category);
    var chevron = $('#chevron-' + category);
    if (detail.is(':visible')) {
        detail.slideUp(200);
        chevron.removeClass('fa-chevron-up').addClass('fa-chevron-down');
    } else {
        detail.slideDown(200);
        chevron.removeClass('fa-chevron-down').addClass('fa-chevron-up');
    }
}

function updateSelectedCount() {
    var count = $('.import-category-check:checked').length;
    $('#selectedCount').text(count);
    $('#confirmImportBtn').prop('disabled', count === 0);
}

function confirmImport() {
    var importId = $('#docImportId').val();
    var categories = [];

    $('.import-category-check:checked').each(function() {
        categories.push($(this).data('category'));
    });

    if (categories.length === 0) {
        showImportError('Please select at least one category to import.');
        return;
    }

    $('#confirmImportBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Importing...');
    $('#importError').hide();

    $.ajax({
        url: docImportBaseUrl + '/confirm',
        type: 'POST',
        data: { import_id: importId, categories: categories },
        success: function(response) {
            if (response.status) {
                renderImportSummary(response.summary, categories);
                $('#importStep2').hide();
                $('#importStep3').show();

                var clientId = $('#docImportClientId').val();
                if (clientId) {
                    loadDocumentList(clientId);
                    loadImportHistory(clientId);
                }
            } else {
                showImportError(response.error || 'Import failed.');
                $('#confirmImportBtn').prop('disabled', false).html('<i class="bx bx-check"></i> Confirm Import (<span id="selectedCount">' + categories.length + '</span> categories)');
            }
        },
        error: function(xhr) {
            var msg = 'Import failed.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                msg = xhr.responseJSON.error;
            }
            showImportError(msg);
            $('#confirmImportBtn').prop('disabled', false).html('<i class="bx bx-check"></i> Confirm Import (<span id="selectedCount">' + categories.length + '</span> categories)');
        }
    });
}

function renderImportSummary(summary, selectedCategories) {
    var container = $('#importSummaryContainer');
    container.empty();

    var allCategories = ['care_history', 'medications', 'risk_assessments', 'client_profile', 'body_map', 'dols'];

    allCategories.forEach(function(cat) {
        var label = categoryLabels[cat] || cat;
        var wasSelected = selectedCategories.indexOf(cat) !== -1;
        var count = summary[cat] || 0;

        if (wasSelected && count > 0) {
            var unit = cat === 'client_profile' ? ' fields updated' :
                       cat === 'medications' ? ' medications added to MAR sheets' :
                       cat === 'risk_assessments' ? ' risk assessments added' :
                       cat === 'care_history' ? ' care history entries added' :
                       cat === 'body_map' ? ' body map entries added' :
                       cat === 'dols' ? ' DoLS records added' : ' items';
            container.append(
                '<div class="d-flex align-items-center mb-2">' +
                '<i class="fa fa-check-circle" style="color:#27ae60;margin-right:10px;"></i>' +
                '<span>Imported ' + count + unit + '</span></div>'
            );
        } else if (wasSelected && count === 0) {
            container.append(
                '<div class="d-flex align-items-center mb-2">' +
                '<i class="fa fa-info-circle" style="color:#f39c12;margin-right:10px;"></i>' +
                '<span>' + esc(label) + ' &mdash; no data to import</span></div>'
            );
        }
    });

    container.append(
        '<div class="d-flex align-items-center mt-3 text-muted">' +
        '<i class="fa fa-file" style="margin-right:10px;"></i>' +
        '<span>Document saved to client files.</span></div>'
    );
}

function loadDocumentList(clientId) {
    $.ajax({
        url: docImportBaseUrl + '/documents',
        type: 'GET',
        data: { client_id: clientId },
        success: function(response) {
            if (response.status) {
                renderDocumentList(response.documents);
            }
        },
        error: function() {
            $('#documentListContainer').html('<p class="text-muted text-center p-4">Could not load documents.</p>');
        }
    });
}

function renderDocumentList(documents) {
    var container = $('#documentListContainer');
    container.empty();

    if (!documents || documents.length === 0) {
        container.html(
            '<div class="text-center p-4 text-muted">' +
            '<i class="far fa-folder-open" style="font-size:48px;"></i>' +
            '<p class="mt-2">No documents uploaded yet.</p>' +
            '<p><small>Click "Import Documents" to upload and analyse a PDF.</small></p></div>'
        );
        return;
    }

    documents.forEach(function(doc) {
        var html = '<div class="bBorderCard mt-3">';
        html += '<div class="d-flex justify-content-between">';
        html += '<div class="bCardHead">';
        html += '<div><i class="far fa-file-pdf" style="color:#e74c3c;"></i></div>';
        html += '<div><h3>' + esc(doc.filename) + '</h3></div>';
        html += '<div><span class="careBadg">' + esc(doc.category) + '</span></div>';
        html += '</div>';
        html += '</div>';
        html += '<div class="docPlanD">';
        if (doc.created_at) {
            html += '<p class="mb-2"><strong>Uploaded:</strong> <span>' + esc(doc.created_at) + '</span></p>';
        }
        if (doc.ai_import && doc.ai_import.summary) {
            var summaryParts = [];
            var s = doc.ai_import.summary;
            if (s.care_history) summaryParts.push(s.care_history + ' care history');
            if (s.medications) summaryParts.push(s.medications + ' medications');
            if (s.risk_assessments) summaryParts.push(s.risk_assessments + ' risk assessments');
            if (s.client_profile) summaryParts.push(s.client_profile + ' profile fields');
            if (s.body_map) summaryParts.push(s.body_map + ' body map');
            if (s.dols) summaryParts.push(s.dols + ' DoLS');
            if (summaryParts.length > 0) {
                html += '<p class="mb-2"><span class="careBadg" style="background:#e8f5e9;color:#2e7d32;">AI Imported</span> ' + esc(summaryParts.join(', ')) + '</p>';
            }
        }
        html += '</div>';
        html += '</div>';
        container.append(html);
    });
}

function loadImportHistory(clientId) {
    $.ajax({
        url: docImportBaseUrl + '/list',
        type: 'GET',
        data: { client_id: clientId },
        success: function(response) {
            if (response.status && response.imports && response.imports.length > 0) {
                renderImportHistory(response.imports);
            } else {
                $('#importHistoryContainer').empty();
            }
        }
    });
}

function renderImportHistory(imports) {
    var container = $('#importHistoryContainer');
    container.empty();

    container.append('<h5 class="mb-3"><i class="bx bx-history"></i> Import History</h5>');

    imports.forEach(function(imp) {
        var statusBadge = '';
        if (imp.status === 'completed') {
            statusBadge = '<span class="careBadg" style="background:#e8f5e9;color:#2e7d32;">Completed</span>';
        } else if (imp.status === 'failed') {
            statusBadge = '<span class="careBadg" style="background:#fde8e8;color:#c62828;">Failed</span>';
        } else if (imp.status === 'extracted') {
            statusBadge = '<span class="careBadg" style="background:#fff3e0;color:#e65100;">Pending Review</span>';
        } else {
            statusBadge = '<span class="careBadg">' + esc(imp.status) + '</span>';
        }

        var html = '<div class="bBorderCard mt-2" style="padding:12px;">';
        html += '<div class="d-flex justify-content-between align-items-center">';
        html += '<div>';
        html += '<i class="far fa-file-pdf" style="color:#e74c3c;"></i> ';
        html += '<strong>' + esc(imp.filename) + '</strong> ';
        html += statusBadge;
        html += '<small class="text-muted ml-2">' + esc(imp.created_at || '') + '</small>';
        if (imp.file_size) html += ' <small class="text-muted">(' + formatFileSize(imp.file_size) + ')</small>';
        html += '</div>';
        html += '<div class="d-flex gap-2">';
        html += '<a href="' + docImportBaseUrl + '/download/' + imp.id + '" class="btn btn-sm btn-outline-secondary" title="Download"><i class="fa fa-download"></i></a>';
        html += '<button class="btn btn-sm btn-outline-danger" onclick="deleteImport(' + imp.id + ')" title="Delete"><i class="fa fa-trash-o"></i></button>';
        html += '</div>';
        html += '</div>';

        if (imp.import_summary) {
            var parts = [];
            var s = imp.import_summary;
            if (s.care_history) parts.push(s.care_history + ' care history');
            if (s.medications) parts.push(s.medications + ' medications');
            if (s.risk_assessments) parts.push(s.risk_assessments + ' risk assessments');
            if (s.client_profile) parts.push(s.client_profile + ' profile fields');
            if (s.body_map) parts.push(s.body_map + ' body map');
            if (s.dols) parts.push(s.dols + ' DoLS');
            if (parts.length > 0) {
                html += '<p class="mb-0 mt-1"><small class="text-muted">Imported: ' + esc(parts.join(', ')) + '</small></p>';
            }
        }

        html += '</div>';
        container.append(html);
    });
}

function deleteImport(importId) {
    if (!confirm('Delete this import record?')) return;

    $.ajax({
        url: docImportBaseUrl + '/delete',
        type: 'POST',
        data: { import_id: importId },
        success: function(response) {
            if (response.status) {
                var clientId = $('#docImportClientId').val();
                if (clientId) {
                    loadDocumentList(clientId);
                    loadImportHistory(clientId);
                }
            }
        }
    });
}

function showImportError(message) {
    $('#importError').show();
    $('#importErrorText').text(message);
}

function resetUploadUI() {
    $('#uploadProgress').hide();
    $('#uploadBtn').prop('disabled', false);
}
