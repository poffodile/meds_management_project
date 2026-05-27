(function() {
    'use strict';

    function esc(str) {
        if (!str && str !== 0) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    var baseUrl = $('meta[name="base-url"]').attr('content') || window.location.origin;
    var csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val();
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': csrfToken} });

    var clientId = null;
    var currentStatus = 'all';
    var currentDetailId = null;
    var currentGridDate = null;

    var CODE_MAP = {
        'A': { label: 'Administered', color: '#27ae60', icon: '&#10003;', given: true },
        'S': { label: 'Self-administered', color: '#2ecc71', icon: '&#10003;', given: true },
        'R': { label: 'Refused', color: '#e74c3c', icon: '&#10007;', given: false },
        'W': { label: 'Withheld', color: '#f39c12', icon: '&#8856;', given: false },
        'N': { label: 'Not Available', color: '#95a5a6', icon: '&mdash;', given: false },
        'O': { label: 'Other', color: '#7f8c8d', icon: '?', given: false }
    };

    var ROUTE_MAP = {
        'Oral': 'Oral',
        'Topical': 'Topical',
        'Inhaled': 'Inhaled',
        'Injection': 'Injection',
        'Sublingual': 'Sublingual',
        'Rectal': 'Rectal',
        'Other': 'Other'
    };

    // ==================== INITIALIZATION ====================

    var marInitialized = false;

    window.initMARSheets = function(cId) {
        clientId = cId;
        currentGridDate = new Date().toISOString().split('T')[0];
        marInitialized = true;
        loadPrescriptions();
    };

    $(document).ready(function() {
        // Auto-init when MAR Sheets tab is clicked (via the medication tab → MAR sub-tab)
        $(document).on('click', '#marSheetBtn', function() {
            if (!marInitialized && typeof client_id !== 'undefined') {
                initMARSheets(client_id);
            } else if (marInitialized) {
                loadPrescriptions();
            }
        });
    });

    // ==================== PRESCRIPTION LIST ====================

    function loadPrescriptions(pageUrl) {
        var url = pageUrl || (baseUrl + '/roster/client/mar-sheet-list');
        $.ajax({
            url: url,
            type: 'POST',
            data: { client_id: clientId, status: currentStatus, _token: csrfToken },
            success: function(res) {
                if (res.success) {
                    renderPrescriptionList(res.data || []);
                    renderPagination(res);
                    $('#countMarSheet').text('(' + (res.total || 0) + ')');
                } else {
                    $('#mar-sheet-list').html('<div class="text-center p-20" style="color:#888;">Could not load prescriptions.</div>');
                }
            },
            error: function() {
                $('#mar-sheet-list').html('<div class="text-center p-20" style="color:#888;">Error loading prescriptions. Please try again.</div>');
            }
        });
    }

    function renderPrescriptionList(items) {
        if (!items.length) {
            $('#mar-sheet-list').html(
                '<div class="text-center" style="padding:40px 20px;color:#888;">' +
                '<i class="fa fa-medkit" style="font-size:36px;margin-bottom:10px;display:block;color:#b8b8d4;"></i>' +
                '<p>No prescriptions found. Click "Add Prescription" to create one.</p></div>'
            );
            return;
        }

        var html = '';
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            var isActive = item.mar_status === 'active';
            var borderClass = isActive ? 'borderleftGreen' : 'borderleftGrey';
            var statusBadge = isActive
                ? '<span class="roundTag greenShowbtn">Active</span>'
                : '<span class="roundTag" style="background:#95a5a6;color:#fff;">Discontinued</span>';

            var timeSlotsHtml = '';
            var slots = item.time_slots || [];
            for (var s = 0; s < slots.length; s++) {
                timeSlotsHtml += '<span class="roundTag" style="background:#e8e8f4;color:#555;font-size:11px;margin-right:3px;">' + esc(slots[s]) + '</span>';
            }

            var prnBadge = item.as_required ? '<span class="roundTag" style="background:#f0ad4e;color:#fff;font-size:11px;">PRN</span> ' : '';

            var stockHtml = '';
            if (item.stock_level !== null && item.stock_level !== undefined) {
                var stockColor = '#27ae60';
                if (item.reorder_level && item.stock_level <= item.reorder_level) stockColor = '#f39c12';
                if (item.stock_level === 0) stockColor = '#e74c3c';
                stockHtml = '<div><strong>Stock:</strong> <span style="color:' + stockColor + ';font-weight:600;">' + esc(item.stock_level) + '</span></div>';
            }

            var createdBy = (item.created_by_user && item.created_by_user.name) ? esc(item.created_by_user.name) : 'Unknown';

            var actionsHtml = '<button class="btn btn-xs btn-default mar-view-btn" data-id="' + item.id + '" title="View MAR"><i class="fa fa-eye"></i> View MAR</button> ';
            if (isActive) {
                actionsHtml += '<button class="btn btn-xs btn-default mar-edit-btn" data-id="' + item.id + '" title="Edit"><i class="fa fa-pencil"></i></button> ';
                actionsHtml += '<button class="btn btn-xs btn-warning mar-discontinue-btn" data-id="' + item.id + '" data-name="' + esc(item.medication_name) + '" title="Discontinue"><i class="fa fa-ban"></i></button> ';
            }
            actionsHtml += '<button class="btn btn-xs btn-danger mar-delete-btn" data-id="' + item.id + '" data-name="' + esc(item.medication_name) + '" title="Delete"><i class="fa fa-trash"></i></button>';

            html += '<div class="planCard ' + borderClass + '">' +
                '<div class="planTop">' +
                    '<div class="planTitle">' +
                        '<span class="statIcon heartIcon ' + (isActive ? 'icongreen' : '') + '"><i class="fa fa-medkit"></i></span> ' +
                        esc(item.medication_name) +
                        (item.dosage ? ' <span class="roundTag" style="background:#e8e8f4;color:#555;">' + esc(item.dosage) + '</span>' : '') +
                        ' ' + prnBadge + statusBadge +
                    '</div>' +
                    '<div class="planActions">' + actionsHtml + '</div>' +
                '</div>' +
                '<div class="planMeta">' +
                    (item.route ? '<div><strong>Route:</strong> ' + esc(item.route) + '</div>' : '') +
                    (item.frequency ? '<div><strong>Frequency:</strong> ' + esc(item.frequency) + '</div>' : '') +
                    (timeSlotsHtml ? '<div><strong>Times:</strong> ' + timeSlotsHtml + '</div>' : '') +
                    stockHtml +
                    (item.prescribed_by ? '<div><strong>Prescribed by:</strong> ' + esc(item.prescribed_by) + '</div>' : '') +
                    '<div><strong>Added by:</strong> ' + createdBy + '</div>' +
                '</div>' +
            '</div>';
        }

        $('#mar-sheet-list').html(html);
    }

    function renderPagination(res) {
        var html = '';
        if (res.prev_page_url || res.next_page_url) {
            html += '<div class="text-center m-t-10">';
            if (res.prev_page_url) {
                html += '<button class="btn btn-sm btn-default mar-page-btn" data-url="' + esc(res.prev_page_url) + '"><i class="fa fa-chevron-left"></i> Prev</button> ';
            }
            if (res.next_page_url) {
                html += '<button class="btn btn-sm btn-default mar-page-btn" data-url="' + esc(res.next_page_url) + '">Next <i class="fa fa-chevron-right"></i></button>';
            }
            html += '</div>';
        }
        $('#mar-sheet-pagination').html(html);
    }

    // ==================== PRESCRIPTION FORM ====================

    function resetPrescriptionForm() {
        $('#marPrescriptionForm')[0].reset();
        $('#mar_sheet_id').val('');
        $('#mar-time-slots-container').html(
            '<div class="input-group m-b-5 time-slot-row">' +
            '<input type="time" class="form-control mar-time-slot-input" name="time_slots[]" value="08:00">' +
            '<span class="input-group-btn"><button type="button" class="btn btn-danger remove-time-slot"><i class="fa fa-times"></i></button></span>' +
            '</div>'
        );
        $('#mar-prn-fields').hide();
    }

    function populateEditForm(item) {
        resetPrescriptionForm();
        $('#mar_sheet_id').val(item.id);
        $('#mar_medication_name').val(item.medication_name || '');
        $('#mar_dosage').val(item.dosage || '');
        $('#mar_dose').val(item.dose || '');
        $('#mar_route').val(item.route || '');
        $('#mar_frequency').val(item.frequency || '');
        $('#mar_as_required').prop('checked', !!item.as_required);
        if (item.as_required) $('#mar-prn-fields').show();
        $('#mar_prn_details').val(item.prn_details || '');
        $('#mar_reason_for_medication').val(item.reason_for_medication || '');
        $('#mar_prescribed_by').val(item.prescribed_by || '');
        $('#mar_prescriber').val(item.prescriber || '');
        $('#mar_pharmacy').val(item.pharmacy || '');
        $('#mar_start_date').val(item.start_date ? item.start_date.split('T')[0] : '');
        $('#mar_end_date').val(item.end_date ? item.end_date.split('T')[0] : '');
        $('#mar_stock_level').val(item.stock_level !== null ? item.stock_level : '');
        $('#mar_reorder_level').val(item.reorder_level !== null ? item.reorder_level : '');
        $('#mar_storage_requirements').val(item.storage_requirements || '');
        $('#mar_allergies_warnings').val(item.allergies_warnings || '');

        var slots = item.time_slots || [];
        if (slots.length) {
            var slotsHtml = '';
            for (var i = 0; i < slots.length; i++) {
                slotsHtml += '<div class="input-group m-b-5 time-slot-row">' +
                    '<input type="time" class="form-control mar-time-slot-input" name="time_slots[]" value="' + esc(slots[i]) + '">' +
                    '<span class="input-group-btn"><button type="button" class="btn btn-danger remove-time-slot"><i class="fa fa-times"></i></button></span></div>';
            }
            $('#mar-time-slots-container').html(slotsHtml);
        }

        $('.marPrescriptionFormWrapper').slideDown();
        $('html, body').animate({ scrollTop: $('.marPrescriptionFormWrapper').offset().top - 100 }, 300);
    }

    function savePrescription() {
        var id = $('#mar_sheet_id').val();
        var url = id
            ? baseUrl + '/roster/client/mar-sheet-update'
            : baseUrl + '/roster/client/mar-sheet-save';

        var data = {
            _token: csrfToken,
            client_id: clientId,
            medication_name: $('#mar_medication_name').val(),
            dosage: $('#mar_dosage').val(),
            dose: $('#mar_dose').val(),
            route: $('#mar_route').val(),
            frequency: $('#mar_frequency').val(),
            as_required: $('#mar_as_required').is(':checked') ? 1 : 0,
            prn_details: $('#mar_prn_details').val(),
            reason_for_medication: $('#mar_reason_for_medication').val(),
            prescribed_by: $('#mar_prescribed_by').val(),
            prescriber: $('#mar_prescriber').val(),
            pharmacy: $('#mar_pharmacy').val(),
            start_date: $('#mar_start_date').val(),
            end_date: $('#mar_end_date').val(),
            stock_level: $('#mar_stock_level').val(),
            reorder_level: $('#mar_reorder_level').val(),
            storage_requirements: $('#mar_storage_requirements').val(),
            allergies_warnings: $('#mar_allergies_warnings').val(),
            time_slots: []
        };

        if (id) data.id = id;

        $('.mar-time-slot-input').each(function() {
            var v = $(this).val();
            if (v) data.time_slots.push(v);
        });

        if (!data.medication_name) {
            alert('Medication name is required.');
            return;
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            success: function(res) {
                if (res.success) {
                    resetPrescriptionForm();
                    $('.marPrescriptionFormWrapper').slideUp();
                    loadPrescriptions();
                } else {
                    alert(res.message || 'Could not save prescription.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var msgs = [];
                    var errs = xhr.responseJSON.errors;
                    for (var k in errs) { msgs.push(errs[k][0]); }
                    alert('Validation errors:\n' + msgs.join('\n'));
                } else {
                    alert('Error saving prescription. Please try again.');
                }
            }
        });
    }

    // ==================== DETAIL VIEW ====================

    function loadDetails(id) {
        $.ajax({
            url: baseUrl + '/roster/client/mar-sheet-details',
            type: 'POST',
            data: { id: id, _token: csrfToken },
            success: function(res) {
                if (res.success && res.data) {
                    currentDetailId = id;
                    renderDetailView(res.data);
                    showDetailSection();
                } else {
                    alert('Prescription not found.');
                }
            },
            error: function() {
                alert('Error loading prescription details.');
            }
        });
    }

    function renderDetailView(item) {
        var isActive = item.mar_status === 'active';
        var statusBadge = isActive
            ? '<span class="roundTag greenShowbtn">Active</span>'
            : '<span class="roundTag" style="background:#95a5a6;color:#fff;">Discontinued</span>';

        var headerHtml =
            '<div style="margin-bottom:20px;">' +
                '<h3 style="margin:0 0 5px;">' + esc(item.medication_name) +
                (item.dosage ? ' <span style="color:#888;font-weight:normal;">(' + esc(item.dosage) + ')</span>' : '') +
                ' ' + statusBadge + '</h3>' +
                '<div style="color:#666;">' +
                    (item.route ? '<span style="margin-right:15px;"><strong>Route:</strong> ' + esc(item.route) + '</span>' : '') +
                    (item.frequency ? '<span style="margin-right:15px;"><strong>Frequency:</strong> ' + esc(item.frequency) + '</span>' : '') +
                    (item.prescribed_by ? '<span style="margin-right:15px;"><strong>Prescribed by:</strong> ' + esc(item.prescribed_by) + '</span>' : '') +
                    (item.pharmacy ? '<span><strong>Pharmacy:</strong> ' + esc(item.pharmacy) + '</span>' : '') +
                '</div>' +
            '</div>';

        $('#mar-detail-header').html(headerHtml);

        // Prescription details panel
        var detailRows = '';
        if (item.dose) detailRows += detailRow('Dose', item.dose);
        if (item.reason_for_medication) detailRows += detailRow('Reason', item.reason_for_medication);
        if (item.prescriber) detailRows += detailRow('Prescriber', item.prescriber);
        if (item.start_date) detailRows += detailRow('Start Date', formatDate(item.start_date));
        if (item.end_date) detailRows += detailRow('End Date', formatDate(item.end_date));
        if (item.stock_level !== null && item.stock_level !== undefined) {
            var stockColor = '#27ae60';
            if (item.reorder_level && item.stock_level <= item.reorder_level) stockColor = '#f39c12';
            if (item.stock_level === 0) stockColor = '#e74c3c';
            detailRows += '<div class="borderB pt-3 pb-3"><h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">Stock Level</h4><div class="text-gray-900"><p style="color:' + stockColor + ';font-weight:600;">' + esc(item.stock_level) + (item.reorder_level ? ' (reorder at ' + esc(item.reorder_level) + ')' : '') + '</p></div></div>';
        }
        if (item.storage_requirements) detailRows += detailRow('Storage', item.storage_requirements);
        if (item.allergies_warnings) detailRows += '<div class="borderB pt-3 pb-3"><h4 class="fontSemibold textSm text-gray-600 uppercase mb-3" style="color:#e74c3c;">Allergies / Warnings</h4><div class="text-gray-900"><p style="color:#e74c3c;">' + esc(item.allergies_warnings) + '</p></div></div>';
        if (item.as_required && item.prn_details) detailRows += detailRow('PRN Details', item.prn_details);
        if (item.discontinued && item.discontinued_reason) detailRows += detailRow('Discontinued Reason', item.discontinued_reason);
        if (item.discontinued_date) detailRows += detailRow('Discontinued Date', formatDate(item.discontinued_date));

        var detailHtml = '<div class="panel panel-default" style="margin-top:15px;">' +
            '<div class="panel-heading" style="cursor:pointer;" id="mar-detail-toggle"><strong><i class="fa fa-info-circle"></i> Prescription Details</strong> <i class="fa fa-chevron-down pull-right"></i></div>' +
            '<div class="panel-body" id="mar-detail-body" style="display:none;">' + detailRows + '</div></div>';
        $('#mar-detail-info').html(detailHtml);

        // Grid date picker
        if (!currentGridDate) currentGridDate = new Date().toISOString().split('T')[0];
        $('#mar-grid-date').val(currentGridDate);

        // Load grid
        if (isActive) {
            loadDetailGrid(item);
        } else {
            $('#mar-grid-container').html('<div class="text-center p-20" style="color:#888;">This prescription is discontinued. No administration grid available.</div>');
        }

        // Administration history
        renderAdminHistory(item.administrations || []);
    }

    function detailRow(label, value) {
        return '<div class="borderB pt-3 pb-3"><h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">' + esc(label) + '</h4><div class="text-gray-900"><p>' + esc(value) + '</p></div></div>';
    }

    function loadDetailGrid(item) {
        var date = $('#mar-grid-date').val() || currentGridDate;
        var slots = item.time_slots || [];
        var admins = (item.administrations || []).filter(function(a) {
            var aDate = a.date ? a.date.split('T')[0] : '';
            return aDate === date;
        });

        if (!slots.length && item.as_required) {
            var prnHtml = '<div class="text-center p-20">' +
                '<p style="color:#888;margin-bottom:10px;">PRN medication &mdash; no scheduled time slots</p>';
            if (item.prn_details) {
                prnHtml += '<p style="color:#666;font-style:italic;margin-bottom:15px;">' + esc(item.prn_details) + '</p>';
            }
            prnHtml += '<button class="btn allbuttonDarkClr mar-record-prn-btn" data-id="' + item.id + '" data-name="' + esc(item.medication_name) + '" data-dosage="' + esc(item.dosage || '') + '"><i class="fa fa-plus"></i> Record PRN Dose</button></div>';
            $('#mar-grid-container').html(prnHtml);
            return;
        }

        if (!slots.length) {
            $('#mar-grid-container').html('<div class="text-center p-20" style="color:#888;">No time slots defined for this prescription.</div>');
            return;
        }

        var gridHtml = '<table class="table table-bordered" style="text-align:center;">' +
            '<thead><tr><th>Time Slot</th><th>Status</th><th>Staff</th><th>Notes</th><th>Action</th></tr></thead><tbody>';

        for (var i = 0; i < slots.length; i++) {
            var slot = slots[i];
            var admin = null;
            for (var j = 0; j < admins.length; j++) {
                if (admins[j].time_slot === slot) { admin = admins[j]; break; }
            }

            if (admin) {
                var cInfo = CODE_MAP[admin.code] || CODE_MAP['O'];
                var staffName = (admin.administered_by_user && admin.administered_by_user.name) ? esc(admin.administered_by_user.name) : 'Staff';
                gridHtml += '<tr style="background-color:' + cInfo.color + '15;">' +
                    '<td><strong>' + esc(slot) + '</strong></td>' +
                    '<td style="color:' + cInfo.color + ';font-weight:600;">' + cInfo.icon + ' ' + esc(cInfo.label) + '</td>' +
                    '<td>' + staffName + (admin.witnessed_by ? '<br><small>Witness: ' + esc(admin.witnessed_by) + '</small>' : '') + '</td>' +
                    '<td>' + esc(admin.notes || '') + '</td>' +
                    '<td><button class="btn btn-xs btn-default mar-administer-btn" data-sheet-id="' + item.id + '" data-slot="' + esc(slot) + '" data-name="' + esc(item.medication_name) + '" data-dosage="' + esc(item.dosage || '') + '" title="Update"><i class="fa fa-pencil"></i></button></td>' +
                    '</tr>';
            } else {
                gridHtml += '<tr>' +
                    '<td><strong>' + esc(slot) + '</strong></td>' +
                    '<td style="color:#ccc;">&mdash; Not recorded</td>' +
                    '<td>&mdash;</td><td>&mdash;</td>' +
                    '<td><button class="btn btn-xs allbuttonDarkClr mar-administer-btn" data-sheet-id="' + item.id + '" data-slot="' + esc(slot) + '" data-name="' + esc(item.medication_name) + '" data-dosage="' + esc(item.dosage || '') + '"><i class="fa fa-plus"></i> Record</button></td>' +
                    '</tr>';
            }
        }

        gridHtml += '</tbody></table>';

        // Legend
        gridHtml += '<div style="margin-top:10px;font-size:12px;color:#666;">' +
            '<strong>Legend:</strong> ';
        for (var code in CODE_MAP) {
            gridHtml += '<span style="margin-right:10px;color:' + CODE_MAP[code].color + ';">' + CODE_MAP[code].icon + ' ' + code + '=' + esc(CODE_MAP[code].label) + '</span>';
        }
        gridHtml += '</div>';

        if (item.as_required) {
            gridHtml += '<div style="margin-top:15px;padding:10px;background:#fef9e7;border:1px solid #f0ad4e;border-radius:4px;">';
            if (item.prn_details) {
                gridHtml += '<p style="color:#666;font-style:italic;margin-bottom:8px;">' + esc(item.prn_details) + '</p>';
            }
            gridHtml += '<button class="btn allbuttonDarkClr mar-record-prn-btn" data-id="' + item.id + '" data-name="' + esc(item.medication_name) + '" data-dosage="' + esc(item.dosage || '') + '"><i class="fa fa-plus"></i> Record PRN Dose</button></div>';
        }

        $('#mar-grid-container').html(gridHtml);
    }

    function renderAdminHistory(admins) {
        if (!admins || !admins.length) {
            $('#mar-admin-history').html('<div class="text-center p-20" style="color:#888;">No administration history yet.</div>');
            return;
        }

        var html = '<div style="max-height:300px;overflow-y:auto;">';
        var shown = admins.slice(0, 20);
        for (var i = 0; i < shown.length; i++) {
            var a = shown[i];
            var cInfo = CODE_MAP[a.code] || CODE_MAP['O'];
            var staffName = (a.administered_by_user && a.administered_by_user.name) ? esc(a.administered_by_user.name) : 'Staff';
            html += '<div style="padding:8px 0;border-bottom:1px solid #eee;">' +
                '<span style="color:' + cInfo.color + ';font-weight:600;">' + cInfo.icon + ' ' + esc(cInfo.label) + '</span> ' +
                '<span style="color:#888;">' + formatDate(a.date) + ' at ' + esc(a.time_slot) + '</span> ' +
                'by ' + staffName +
                (a.dose_given ? ' <span style="color:#555;">(' + esc(a.dose_given) + ')</span>' : '') +
                (a.witnessed_by ? ' <span style="color:#888;">[Witness: ' + esc(a.witnessed_by) + ']</span>' : '') +
                (a.notes ? '<div style="color:#666;font-size:12px;margin-top:2px;">' + esc(a.notes) + '</div>' : '') +
            '</div>';
        }
        if (admins.length > 20) {
            html += '<div class="text-center" style="padding:8px;color:#888;">Showing 20 of ' + admins.length + ' records</div>';
        }
        html += '</div>';
        $('#mar-admin-history').html(html);
    }

    // ==================== SECTION TOGGLE ====================

    function showDetailSection() {
        $('.medicationSectionFirst').hide();
        $('.medicationSectionSecond').show();
    }

    function showListSection() {
        $('.medicationSectionSecond').hide();
        $('.medicationSectionFirst').show();
        currentDetailId = null;
    }

    // ==================== ADMINISTER MODAL ====================

    function openAdministerModal(sheetId, slot, medName, dosage) {
        var date = $('#mar-grid-date').val() || currentGridDate;
        $('#admin-modal-med-name').text(medName);
        $('#admin-modal-slot').text(slot);
        $('#admin-modal-date').text(date);
        $('#admin_mar_sheet_id').val(sheetId);
        $('#admin_time_slot').val(slot);
        $('#admin_date').val(date);
        $('#admin_dose_given').val(dosage || '');
        $('#admin_code').val('A');
        $('#admin_witnessed_by').val('');
        $('#admin_notes').val('');
        $('#marAdministerModal').modal('show');
    }

    function saveAdministration() {
        var data = {
            _token: csrfToken,
            mar_sheet_id: $('#admin_mar_sheet_id').val(),
            date: $('#admin_date').val(),
            time_slot: $('#admin_time_slot').val(),
            code: $('#admin_code').val(),
            dose_given: $('#admin_dose_given').val(),
            witnessed_by: $('#admin_witnessed_by').val(),
            notes: $('#admin_notes').val()
        };

        $.ajax({
            url: baseUrl + '/roster/client/mar-administer',
            type: 'POST',
            data: data,
            success: function(res) {
                if (res.success) {
                    $('#marAdministerModal').modal('hide');
                    if (currentDetailId) loadDetails(currentDetailId);
                } else {
                    alert(res.message || 'Could not record administration.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var msgs = [];
                    var errs = xhr.responseJSON.errors;
                    for (var k in errs) { msgs.push(errs[k][0]); }
                    alert('Validation errors:\n' + msgs.join('\n'));
                } else {
                    alert('Error recording administration. Please try again.');
                }
            }
        });
    }

    // ==================== ACTIONS ====================

    function deletePrescription(id, name) {
        if (!confirm('Are you sure you want to delete "' + name + '"? This action cannot be undone.')) return;
        $.ajax({
            url: baseUrl + '/roster/client/mar-sheet-delete',
            type: 'POST',
            data: { id: id, _token: csrfToken },
            success: function(res) {
                if (res.success) {
                    loadPrescriptions();
                } else {
                    alert(res.message || 'Could not delete prescription.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    alert('Only administrators can delete prescriptions.');
                } else {
                    alert('Error deleting prescription. Please try again.');
                }
            }
        });
    }

    function discontinuePrescription(id, name) {
        var reason = prompt('Please provide a reason for discontinuing "' + name + '":');
        if (reason === null) return;
        $.ajax({
            url: baseUrl + '/roster/client/mar-sheet-discontinue',
            type: 'POST',
            data: { id: id, discontinued_reason: reason, _token: csrfToken },
            success: function(res) {
                if (res.success) {
                    loadPrescriptions();
                } else {
                    alert(res.message || 'Could not discontinue prescription.');
                }
            },
            error: function() {
                alert('Error discontinuing prescription. Please try again.');
            }
        });
    }

    // ==================== HELPERS ====================

    function formatDate(d) {
        if (!d) return '';
        var date = new Date(d);
        if (isNaN(date.getTime())) return esc(d);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    // ==================== EVENT HANDLERS ====================

    $(document).ready(function() {
        // Status filter
        $(document).on('click', '.mar-status-filter', function() {
            $('.mar-status-filter').removeClass('active');
            $(this).addClass('active');
            currentStatus = $(this).data('status');
            loadPrescriptions();
        });

        // Pagination
        $(document).on('click', '.mar-page-btn', function() {
            loadPrescriptions($(this).data('url'));
        });

        // Add prescription button
        $(document).on('click', '#addPrescriptionBtn', function() {
            resetPrescriptionForm();
            $('.marPrescriptionFormWrapper').slideToggle();
        });

        // Cancel form
        $(document).on('click', '#cancelMarPrescriptionBtn', function() {
            resetPrescriptionForm();
            $('.marPrescriptionFormWrapper').slideUp();
        });

        // Save prescription
        $(document).on('click', '#saveMarPrescriptionBtn', function() {
            savePrescription();
        });

        // Add time slot
        $(document).on('click', '#addTimeSlotBtn', function() {
            $('#mar-time-slots-container').append(
                '<div class="input-group m-b-5 time-slot-row">' +
                '<input type="time" class="form-control mar-time-slot-input" name="time_slots[]">' +
                '<span class="input-group-btn"><button type="button" class="btn btn-danger remove-time-slot"><i class="fa fa-times"></i></button></span></div>'
            );
        });

        // Remove time slot
        $(document).on('click', '.remove-time-slot', function() {
            $(this).closest('.time-slot-row').remove();
        });

        // PRN toggle
        $(document).on('change', '#mar_as_required', function() {
            if ($(this).is(':checked')) {
                $('#mar-prn-fields').slideDown();
            } else {
                $('#mar-prn-fields').slideUp();
            }
        });

        // View MAR
        $(document).on('click', '.mar-view-btn', function() {
            loadDetails($(this).data('id'));
        });

        // Edit prescription
        $(document).on('click', '.mar-edit-btn', function() {
            var id = $(this).data('id');
            $.ajax({
                url: baseUrl + '/roster/client/mar-sheet-details',
                type: 'POST',
                data: { id: id, _token: csrfToken },
                success: function(res) {
                    if (res.success && res.data) {
                        populateEditForm(res.data);
                    } else {
                        alert('Prescription not found.');
                    }
                },
                error: function() {
                    alert('Error loading prescription.');
                }
            });
        });

        // Delete
        $(document).on('click', '.mar-delete-btn', function() {
            deletePrescription($(this).data('id'), $(this).data('name'));
        });

        // Discontinue
        $(document).on('click', '.mar-discontinue-btn', function() {
            discontinuePrescription($(this).data('id'), $(this).data('name'));
        });

        // Back button
        $(document).on('click', '#medicationBackBtn', function() {
            showListSection();
        });

        // Grid date change
        $(document).on('change', '#mar-grid-date', function() {
            currentGridDate = $(this).val();
            if (currentDetailId) loadDetails(currentDetailId);
        });

        // Administer dose
        $(document).on('click', '.mar-administer-btn', function() {
            openAdministerModal(
                $(this).data('sheet-id'),
                $(this).data('slot'),
                $(this).data('name'),
                $(this).data('dosage')
            );
        });

        // Record PRN
        $(document).on('click', '.mar-record-prn-btn', function() {
            var timeNow = new Date().toTimeString().slice(0, 5);
            openAdministerModal(
                $(this).data('id'),
                timeNow,
                $(this).data('name'),
                $(this).data('dosage')
            );
        });

        // Save administration
        $(document).on('click', '#saveAdministrationBtn', function() {
            saveAdministration();
        });

        // Detail toggle
        $(document).on('click', '#mar-detail-toggle', function() {
            $('#mar-detail-body').slideToggle();
            $(this).find('.fa-chevron-down, .fa-chevron-up').toggleClass('fa-chevron-down fa-chevron-up');
        });
    });

})();
