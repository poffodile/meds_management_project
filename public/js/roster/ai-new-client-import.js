(function() {
    'use strict';

    var uploadedFiles = [];
    var currentImportId = null;

    function esc(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    window.openNewClientImportModal = function() {
        currentImportId = null;
        uploadedFiles = [];
        $('#nciStep1').show();
        $('#nciStep2').hide().empty();
        $('#nciStep3').hide().empty();
        $('#nciLoading').hide();
        $('#nciFileList').hide();
        $('#nciFileItems').empty();
        $('#nciExtractBtn').hide();
        $('#nciFileInput').val('');
        $('#aiNewClientImportModal').modal('show');
    };

    // Drop zone events
    $(document).ready(function() {
        var dropZone = document.getElementById('nciDropZone');
        if (!dropZone) return;

        dropZone.addEventListener('click', function() {
            document.getElementById('nciFileInput').click();
        });

        var fileInput = document.getElementById('nciFileInput');
        if (fileInput) {
            fileInput.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.borderColor = '#337ab7';
            dropZone.style.background = '#f0f7ff';
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.borderColor = '#ddd';
            dropZone.style.background = '#fafafa';
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.borderColor = '#ddd';
            dropZone.style.background = '#fafafa';
            handleFiles(e.dataTransfer.files);
        });

        document.getElementById('nciFileInput').addEventListener('change', function(e) {
            handleFiles(e.target.files);
        });
    });

    function handleFiles(fileList) {
        var allowedTypes = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword',
            'application/octet-stream',
            'application/zip',
            'application/x-zip-compressed'
        ];
        var maxSize = 10 * 1024 * 1024;

        for (var i = 0; i < fileList.length; i++) {
            var file = fileList[i];

            if (file.size === 0) {
                alert('Empty file skipped: ' + file.name + '. The file has no content.');
                continue;
            }

            var fileExt = file.name.split('.').pop().toLowerCase();
            var allowedExts = ['pdf', 'docx', 'doc'];
            if (allowedTypes.indexOf(file.type) === -1 && allowedExts.indexOf(fileExt) === -1) {
                alert('Invalid file type: ' + file.name + '. Only PDF and Word documents are allowed.');
                continue;
            }

            if (file.size > maxSize) {
                alert('File too large: ' + file.name + '. Maximum size is 10MB.');
                continue;
            }

            if (uploadedFiles.length >= 10) {
                alert('Maximum 10 files allowed.');
                break;
            }

            uploadedFiles.push(file);
        }

        renderFileList();
    }

    function renderFileList() {
        if (uploadedFiles.length === 0) {
            $('#nciFileList').hide();
            $('#nciExtractBtn').hide();
            return;
        }

        $('#nciFileList').show();
        $('#nciExtractBtn').show();

        var html = '';
        for (var i = 0; i < uploadedFiles.length; i++) {
            var f = uploadedFiles[i];
            var size = (f.size / 1024).toFixed(1);
            html += '<div class="nci-file-item" style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;border:1px solid #eee;border-radius:4px;margin-bottom:5px;">';
            html += '<span><i class="fa fa-file-text-o"></i> ' + esc(f.name) + ' <small class="text-muted">(' + esc(size) + ' KB)</small></span>';
            html += '<button type="button" class="btn btn-xs btn-default" onclick="removeNciFile(' + i + ')" title="Remove"><i class="fa fa-times"></i></button>';
            html += '</div>';
        }
        $('#nciFileItems').html(html);
    }

    window.removeNciFile = function(index) {
        uploadedFiles.splice(index, 1);
        renderFileList();
    };

    window.uploadAndExtract = function() {
        if (uploadedFiles.length === 0) {
            alert('Please select at least one file.');
            return;
        }

        $('#nciStep1').hide();
        $('#nciLoading').show();
        $('#nciLoadingText').text('Uploading ' + uploadedFiles.length + ' file(s)...');

        var formData = new FormData();
        for (var i = 0; i < uploadedFiles.length; i++) {
            formData.append('files[]', uploadedFiles[i]);
        }

        $.ajax({
            url: nciBaseUrl + '/ai-new-client-import/upload',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {'X-CSRF-TOKEN': nciCsrfToken},
            success: function(res) {
                if (res.status) {
                    currentImportId = res.import_id;
                    extractData(res.import_id);
                } else {
                    showError(res.message || 'Upload failed.');
                }
            },
            error: function(xhr) {
                var msg = 'Upload failed.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errs = xhr.responseJSON.errors;
                    var messages = [];
                    for (var key in errs) {
                        var fieldErrors = errs[key];
                        for (var i = 0; i < fieldErrors.length; i++) {
                            var e = fieldErrors[i];
                            var match = key.match(/files\.(\d+)/);
                            if (match && uploadedFiles[parseInt(match[1])]) {
                                e = uploadedFiles[parseInt(match[1])].name + ': Only PDF and Word (.docx) files are allowed.';
                            }
                            messages.push(e);
                        }
                    }
                    msg = messages.join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showError(msg);
            }
        });
    };

    function extractData(importId) {
        $('#nciLoadingText').text('Analysing documents with AI... This may take 15-30 seconds.');

        $.ajax({
            url: nciBaseUrl + '/ai-new-client-import/extract',
            type: 'POST',
            data: JSON.stringify({import_id: importId}),
            contentType: 'application/json',
            headers: {'X-CSRF-TOKEN': nciCsrfToken},
            timeout: 60000,
            success: function(res) {
                if (res.status) {
                    renderReviewScreen(res.extracted_data, res.duplicate_warning, res.tokens_used);
                } else {
                    showError(res.message || 'AI extraction failed.');
                }
            },
            error: function(xhr) {
                var msg = 'AI extraction failed.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showError(msg);
            }
        });
    }

    function renderReviewScreen(data, duplicateWarning, tokensUsed) {
        $('#nciLoading').hide();
        $('#nciStep2').show();

        var client = data.client || {};
        var html = '';

        // Duplicate warning
        if (duplicateWarning) {
            html += '<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> ';
            html += 'A similar client already exists: <strong>' + esc(duplicateWarning.name) + '</strong>';
            if (duplicateWarning.date_of_birth) {
                html += ' (DOB: ' + esc(duplicateWarning.date_of_birth) + ')';
            }
            html += '. Proceed only if this is a different person.</div>';
        }

        // Client profile card
        html += '<div class="panel panel-primary">';
        html += '<div class="panel-heading"><i class="fa fa-user"></i> Client Profile</div>';
        html += '<div class="panel-body">';
        html += '<div class="row">';
        html += '<div class="col-md-6"><strong>Name:</strong> ' + esc(client.full_name) + '</div>';
        html += '<div class="col-md-3"><strong>DOB:</strong> ' + esc(client.date_of_birth || 'Not found') + '</div>';
        html += '<div class="col-md-3"><strong>Gender:</strong> ' + esc(client.gender || 'Not found') + '</div>';
        html += '</div>';

        if (client.address && (client.address.street || client.address.city || client.address.postcode)) {
            html += '<div class="row" style="margin-top:8px;">';
            html += '<div class="col-md-12"><strong>Address:</strong> ' + esc([client.address.street, client.address.city, client.address.postcode].filter(Boolean).join(', ')) + '</div>';
            html += '</div>';
        }

        if (client.emergency_contact && client.emergency_contact.name) {
            html += '<div class="row" style="margin-top:8px;">';
            html += '<div class="col-md-12"><strong>Emergency Contact:</strong> ' + esc(client.emergency_contact.name);
            if (client.emergency_contact.relationship) html += ' (' + esc(client.emergency_contact.relationship) + ')';
            if (client.emergency_contact.phone) html += ' &mdash; ' + esc(client.emergency_contact.phone);
            html += '</div></div>';
        }

        if (client.care_needs && client.care_needs.length) {
            html += '<div class="row" style="margin-top:8px;">';
            html += '<div class="col-md-12"><strong>Care Needs:</strong> ' + esc(client.care_needs.join(', ')) + '</div>';
            html += '</div>';
        }

        if (client.medical_notes) {
            html += '<div class="row" style="margin-top:8px;">';
            html += '<div class="col-md-12"><strong>Medical Notes:</strong> ' + esc(client.medical_notes) + '</div>';
            html += '</div>';
        }

        if (client.mobility) {
            html += '<div class="row" style="margin-top:8px;">';
            html += '<div class="col-md-6"><strong>Mobility:</strong> ' + esc(client.mobility) + '</div>';
            if (client.funding_type) html += '<div class="col-md-6"><strong>Funding:</strong> ' + esc(client.funding_type) + '</div>';
            html += '</div>';
        }

        if (client.local_authority) {
            html += '<div class="row" style="margin-top:8px;">';
            html += '<div class="col-md-6"><strong>Local Authority:</strong> ' + esc(client.local_authority) + '</div>';
            if (client.child_type) html += '<div class="col-md-6"><strong>Type:</strong> ' + esc(client.child_type) + '</div>';
            html += '</div>';
        }

        html += '</div></div>';

        // Care record categories
        var categories = [
            {key: 'care_history', label: 'Care History', icon: 'fa-history'},
            {key: 'medications', label: 'Medications', icon: 'fa-medkit'},
            {key: 'risk_assessments', label: 'Risk Assessments', icon: 'fa-exclamation-triangle'},
            {key: 'body_map', label: 'Body Map', icon: 'fa-user'},
            {key: 'dols', label: 'DoLS', icon: 'fa-lock'}
        ];

        html += '<h5 style="margin-top:20px;">Care Records to Import:</h5>';

        var hasAnyRecords = false;
        for (var i = 0; i < categories.length; i++) {
            var cat = categories[i];
            var items = data[cat.key] || [];
            var count = items.length;
            var checked = count > 0 ? 'checked' : '';
            var disabled = count === 0 ? 'disabled' : '';

            if (count > 0) hasAnyRecords = true;

            html += '<div class="nci-category" style="border:1px solid #eee;border-radius:4px;padding:10px 15px;margin-bottom:8px;">';
            html += '<div style="display:flex;align-items:center;justify-content:space-between;">';
            html += '<label style="margin:0;cursor:pointer;"><input type="checkbox" class="nci-cat-check" value="' + cat.key + '" ' + checked + ' ' + disabled + '> ';
            html += '<i class="fa ' + cat.icon + '"></i> ' + esc(cat.label) + ' <span class="badge">' + count + '</span></label>';
            if (count > 0) {
                html += '<button type="button" class="btn btn-xs btn-default nci-expand-btn" data-cat="' + cat.key + '"><i class="fa fa-chevron-down"></i></button>';
            }
            html += '</div>';

            if (count > 0) {
                html += '<div class="nci-cat-detail" id="nciDetail_' + cat.key + '" style="display:none;margin-top:10px;padding-top:10px;border-top:1px solid #eee;">';
                html += renderCategoryItems(cat.key, items);
                html += '</div>';
            }
            html += '</div>';
        }

        if (!hasAnyRecords) {
            html += '<p class="text-muted"><i class="fa fa-info-circle"></i> No care records found in the documents. Only the client profile will be created.</p>';
        }

        // Document summary
        if (data.document_summary) {
            html += '<p class="text-muted" style="margin-top:15px;font-size:12px;"><i class="fa fa-info-circle"></i> ' + esc(data.document_summary) + '</p>';
        }

        // Token usage
        if (tokensUsed) {
            html += '<p class="text-muted" style="font-size:11px;">AI tokens used: ' + esc(String(tokensUsed)) + '</p>';
        }

        // Action buttons
        html += '<div style="margin-top:20px;text-align:right;">';
        html += '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button> ';
        html += '<button type="button" class="btn btn-primary" onclick="confirmNewClientImport()">';
        html += '<i class="fa fa-user-plus"></i> Create "' + esc(client.full_name) + '"</button>';
        html += '</div>';

        $('#nciStep2').html(html);

        // Expand/collapse handlers
        $('.nci-expand-btn').off('click').on('click', function() {
            var cat = $(this).data('cat');
            var detail = $('#nciDetail_' + cat);
            var icon = $(this).find('i');
            if (detail.is(':visible')) {
                detail.slideUp(200);
                icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            } else {
                detail.slideDown(200);
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            }
        });
    }

    function renderCategoryItems(category, items) {
        var html = '';
        if (category === 'care_history') {
            for (var i = 0; i < items.length; i++) {
                html += '<div style="margin-bottom:8px;padding:6px 10px;background:#f9f9f9;border-radius:3px;">';
                html += '<strong>' + esc(items[i].title) + '</strong>';
                if (items[i].date) html += ' <small class="text-muted">(' + esc(items[i].date) + ')</small>';
                html += '<br><span style="font-size:12px;">' + esc(items[i].description || '') + '</span>';
                html += '</div>';
            }
        } else if (category === 'medications') {
            for (var i = 0; i < items.length; i++) {
                html += '<div style="margin-bottom:8px;padding:6px 10px;background:#f9f9f9;border-radius:3px;">';
                html += '<strong>' + esc(items[i].medication_name) + '</strong>';
                var parts = [];
                if (items[i].dosage) parts.push(items[i].dosage);
                if (items[i].route) parts.push(items[i].route);
                if (items[i].frequency) parts.push(items[i].frequency);
                if (parts.length) html += ' &mdash; ' + esc(parts.join(', '));
                if (items[i].reason_for_medication) html += '<br><small class="text-muted">' + esc(items[i].reason_for_medication) + '</small>';
                html += '</div>';
            }
        } else if (category === 'risk_assessments') {
            for (var i = 0; i < items.length; i++) {
                var levelClass = items[i].risk_level === 'high' ? 'danger' : (items[i].risk_level === 'medium' ? 'warning' : 'success');
                html += '<div style="margin-bottom:8px;padding:6px 10px;background:#f9f9f9;border-radius:3px;">';
                html += '<strong>' + esc(items[i].risk_type) + '</strong> <span class="label label-' + levelClass + '">' + esc(items[i].risk_level || 'medium') + '</span>';
                if (items[i].description) html += '<br><span style="font-size:12px;">' + esc(items[i].description) + '</span>';
                html += '</div>';
            }
        } else if (category === 'body_map') {
            for (var i = 0; i < items.length; i++) {
                html += '<div style="margin-bottom:8px;padding:6px 10px;background:#f9f9f9;border-radius:3px;">';
                html += '<strong>' + esc(items[i].injury_type || 'Injury') + '</strong>';
                if (items[i].body_part) html += ' (' + esc(items[i].body_part) + ')';
                if (items[i].injury_description) html += '<br><span style="font-size:12px;">' + esc(items[i].injury_description) + '</span>';
                html += '</div>';
            }
        } else if (category === 'dols') {
            for (var i = 0; i < items.length; i++) {
                html += '<div style="margin-bottom:8px;padding:6px 10px;background:#f9f9f9;border-radius:3px;">';
                html += '<strong>' + esc(items[i].dols_status) + '</strong>';
                if (items[i].authorisation_type) html += ' &mdash; ' + esc(items[i].authorisation_type);
                if (items[i].reason_for_dols) html += '<br><span style="font-size:12px;">' + esc(items[i].reason_for_dols) + '</span>';
                html += '</div>';
            }
        }
        return html;
    }

    window.confirmNewClientImport = function() {
        if (!currentImportId) return;

        var selectedCategories = [];
        $('.nci-cat-check:checked').each(function() {
            selectedCategories.push($(this).val());
        });

        $('#nciStep2').hide();
        $('#nciLoading').show();
        $('#nciLoadingText').text('Creating client and importing care records...');

        $.ajax({
            url: nciBaseUrl + '/ai-new-client-import/confirm',
            type: 'POST',
            data: JSON.stringify({
                import_id: currentImportId,
                selected_categories: selectedCategories
            }),
            contentType: 'application/json',
            headers: {'X-CSRF-TOKEN': nciCsrfToken},
            success: function(res) {
                if (res.status) {
                    renderSuccess(res);
                } else {
                    showError(res.message || 'Client creation failed.');
                }
            },
            error: function(xhr) {
                var msg = 'Client creation failed.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showError(msg);
            }
        });
    };

    function renderSuccess(res) {
        $('#nciLoading').hide();
        $('#nciStep3').show();

        var html = '<div style="text-align:center;padding:20px;">';
        html += '<i class="fa fa-check-circle" style="font-size:48px;color:#5cb85c;"></i>';
        html += '<h4 style="margin-top:15px;">Client "' + esc(res.client_name) + '" Created</h4>';

        if (res.summary) {
            var summaryItems = [];
            var labels = {
                care_history: 'care history entries',
                medications: 'medications',
                risk_assessments: 'risk assessments',
                body_map: 'body map entries',
                dols: 'DoLS records'
            };
            for (var key in res.summary) {
                if (res.summary[key] > 0) {
                    summaryItems.push('<li><i class="fa fa-check text-success"></i> Imported ' + res.summary[key] + ' ' + (labels[key] || key) + '</li>');
                }
            }
            if (summaryItems.length) {
                html += '<ul style="list-style:none;padding:0;margin:15px 0;text-align:left;max-width:300px;margin-left:auto;margin-right:auto;">' + summaryItems.join('') + '</ul>';
            }
        }

        html += '<div style="margin-top:20px;">';
        html += '<a href="' + esc(res.redirect_url) + '" class="btn btn-primary"><i class="fa fa-user"></i> View Client Profile</a>';
        html += '</div></div>';

        $('#nciStep3').html(html);
    }

    function showError(message) {
        $('#nciLoading').hide();
        $('#nciStep1').show();
        $('#nciStep2').hide();
        $('#nciStep3').hide();
        alert(message);
    }

})();
