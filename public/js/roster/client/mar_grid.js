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

    var gridClientId = null;
    var gridYear = new Date().getFullYear();
    var gridMonth = new Date().getMonth() + 1;

    var CODE_MAP = {
        'A': { label: 'Administered', color: '#27ae60', css: 'mar-code-a' },
        'S': { label: 'Self-administered', color: '#2ecc71', css: 'mar-code-s' },
        'R': { label: 'Refused', color: '#e74c3c', css: 'mar-code-r' },
        'W': { label: 'Withheld', color: '#f39c12', css: 'mar-code-w' },
        'N': { label: 'Not Available', color: '#95a5a6', css: 'mar-code-n' },
        'O': { label: 'Other', color: '#7f8c8d', css: 'mar-code-o' }
    };

    var DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var MONTH_NAMES = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    window.initMARGrid = function(cId) {
        gridClientId = cId;
        $('#mar-grid-year').val(gridYear);
        $('#mar-grid-month').val(gridMonth);
        loadMonthlyGrid();
    };

    function loadMonthlyGrid() {
        var container = $('#mar-monthly-grid-container');
        container.html('<div class="text-center" style="padding:40px;color:#888;"><i class="fa fa-spinner fa-spin"></i> Loading MAR grid...</div>');

        $.ajax({
            url: baseUrl + '/roster/client/mar-monthly-grid',
            type: 'POST',
            data: { client_id: gridClientId, year: gridYear, month: gridMonth, _token: csrfToken },
            success: function(res) {
                if (res.success && res.data) {
                    renderMonthlyGrid(res.data);
                } else {
                    container.html('<div class="text-center" style="padding:40px;color:#888;">Could not load MAR grid.</div>');
                }
            },
            error: function() {
                container.html('<div class="text-center" style="padding:40px;color:#888;">Error loading MAR grid. Please try again.</div>');
            }
        });
    }

    function renderMonthlyGrid(data) {
        var sheets = data.sheets || [];
        var daysInMonth = data.days_in_month;
        var year = data.year;
        var month = data.month;

        if (!sheets.length) {
            $('#mar-monthly-grid-container').html(
                '<div class="text-center" style="padding:40px;color:#888;">' +
                '<i class="fa fa-medkit" style="font-size:36px;margin-bottom:10px;display:block;color:#b8b8d4;"></i>' +
                '<p>No active prescriptions for ' + esc(MONTH_NAMES[month - 1]) + ' ' + year + '.</p></div>'
            );
            return;
        }

        var html = '<div class="mar-grid-scroll">';
        html += '<table class="mar-grid-table">';

        // Header row — day numbers
        html += '<thead><tr>';
        html += '<th class="mar-grid-med-col">Medication Details</th>';
        html += '<th class="mar-grid-time-col">Time</th>';
        for (var d = 1; d <= daysInMonth; d++) {
            var dayDate = new Date(year, month - 1, d);
            var dayName = DAY_NAMES[dayDate.getDay()];
            var isWeekStart = d > 1 && dayDate.getDay() === 1;
            var isSunday = dayDate.getDay() === 0;
            var cls = 'mar-grid-day-col';
            if (isWeekStart) cls += ' mar-grid-week-sep';
            if (isSunday) cls += ' mar-grid-sunday';
            html += '<th class="' + cls + '">' + dayName.charAt(0) + '<br>' + d + '</th>';
        }
        html += '<th class="mar-grid-bal-col">Bal</th>';
        html += '</tr></thead>';

        html += '<tbody>';

        for (var i = 0; i < sheets.length; i++) {
            var sheet = sheets[i];
            var timeSlots = sheet.time_slots || [];
            if (!timeSlots.length) timeSlots = ['—'];
            var admins = sheet.administrations || [];

            var adminMap = {};
            for (var a = 0; a < admins.length; a++) {
                var adm = admins[a];
                var aDate = adm.date ? adm.date.split('T')[0] : '';
                if (!adminMap[aDate]) adminMap[aDate] = {};
                adminMap[aDate][adm.time_slot] = adm;
            }

            var givenCount = 0;
            for (var a = 0; a < admins.length; a++) {
                if (admins[a].code === 'A' || admins[a].code === 'S') givenCount++;
            }
            var startBal = (sheet.quantity_received || 0) + (sheet.quantity_carried_forward || 0);
            var currentBal = startBal - givenCount - (sheet.quantity_returned || 0);

            for (var s = 0; s < timeSlots.length; s++) {
                var slot = timeSlots[s];
                html += '<tr class="' + (s === 0 ? 'mar-grid-med-first' : '') + '">';

                if (s === 0) {
                    var medLabel = '<strong>' + esc(sheet.medication_name) + '</strong>';
                    if (sheet.dosage) medLabel += ' <span class="mar-grid-dosage">' + esc(sheet.dosage) + '</span>';
                    if (sheet.dose) medLabel += ' &middot; ' + esc(sheet.dose);
                    if (sheet.route) medLabel += ' <span class="mar-grid-route">(' + esc(sheet.route) + ')</span>';
                    if (sheet.frequency) medLabel += '<br><em class="mar-grid-freq">' + esc(sheet.frequency) + '</em>';
                    if (sheet.as_required) medLabel += ' <span class="mar-grid-prn">PRN</span>';

                    html += '<td class="mar-grid-med-col" rowspan="' + timeSlots.length + '">' + medLabel + '</td>';
                }

                html += '<td class="mar-grid-time-col">' + esc(slot) + '</td>';

                for (var d = 1; d <= daysInMonth; d++) {
                    var dateStr = year + '-' + String(month).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                    var dayDate = new Date(year, month - 1, d);
                    var isWeekStart = d > 1 && dayDate.getDay() === 1;
                    var entry = (adminMap[dateStr] && adminMap[dateStr][slot]) ? adminMap[dateStr][slot] : null;
                    var code = entry ? (entry.code || '') : '';
                    var codeInfo = CODE_MAP[code] || null;

                    var cellCls = 'mar-grid-cell';
                    if (isWeekStart) cellCls += ' mar-grid-week-sep';
                    if (codeInfo) cellCls += ' ' + codeInfo.css;

                    var tooltip = '';
                    if (entry) {
                        var staffName = (entry.administered_by_user && entry.administered_by_user.name) ? entry.administered_by_user.name : '';
                        tooltip = (codeInfo ? codeInfo.label : code) + (staffName ? ' by ' + staffName : '');
                        if (entry.notes) tooltip += ' — ' + entry.notes;
                    }

                    html += '<td class="' + cellCls + '" title="' + esc(tooltip) + '" ' +
                        'data-sheet-id="' + sheet.id + '" data-slot="' + esc(slot) + '" data-date="' + dateStr + '" ' +
                        'data-med-name="' + esc(sheet.medication_name) + '" data-dosage="' + esc(sheet.dosage || '') + '">' +
                        (code || '') + '</td>';
                }

                if (s === 0) {
                    html += '<td class="mar-grid-bal-col" rowspan="' + timeSlots.length + '">' +
                        (startBal > 0 ? currentBal : '') + '</td>';
                }

                html += '</tr>';
            }

            // Stock row
            html += '<tr class="mar-grid-stock-row">';
            html += '<td colspan="2" class="mar-grid-stock-cell">';
            html += '<div class="mar-grid-stock-form" data-sheet-id="' + sheet.id + '">';
            html += '<span class="mar-grid-stock-field"><label>Received:</label> <input type="number" min="0" class="mar-stock-input" data-field="quantity_received" value="' + (sheet.quantity_received != null ? sheet.quantity_received : '') + '"></span>';
            html += '<span class="mar-grid-stock-field"><label>Carried fwd:</label> <input type="number" min="0" class="mar-stock-input" data-field="quantity_carried_forward" value="' + (sheet.quantity_carried_forward != null ? sheet.quantity_carried_forward : '') + '"></span>';
            html += '<span class="mar-grid-stock-field"><label>Returned:</label> <input type="number" min="0" class="mar-stock-input" data-field="quantity_returned" value="' + (sheet.quantity_returned != null ? sheet.quantity_returned : '') + '"></span>';
            html += '<button class="btn btn-xs btn-default mar-stock-save-btn" data-sheet-id="' + sheet.id + '"><i class="fa fa-save"></i> Save</button>';
            html += '</div>';
            html += '</td>';
            html += '<td colspan="' + (daysInMonth + 1) + '"></td>';
            html += '</tr>';
        }

        html += '</tbody></table></div>';

        // Legend
        html += '<div class="mar-grid-legend">';
        html += '<strong>Key:</strong> ';
        for (var code in CODE_MAP) {
            html += '<span class="mar-grid-legend-item ' + CODE_MAP[code].css + '">' + code + ' = ' + esc(CODE_MAP[code].label) + '</span>';
        }
        html += '</div>';

        $('#mar-monthly-grid-container').html(html);
    }

    // ==================== EVENT HANDLERS ====================

    $(document).ready(function() {
        // Month/year navigation
        $(document).on('change', '#mar-grid-year, #mar-grid-month', function() {
            gridYear = parseInt($('#mar-grid-year').val());
            gridMonth = parseInt($('#mar-grid-month').val());
            loadMonthlyGrid();
        });

        $(document).on('click', '#mar-grid-prev-month', function() {
            gridMonth--;
            if (gridMonth < 1) { gridMonth = 12; gridYear--; }
            $('#mar-grid-year').val(gridYear);
            $('#mar-grid-month').val(gridMonth);
            loadMonthlyGrid();
        });

        $(document).on('click', '#mar-grid-next-month', function() {
            gridMonth++;
            if (gridMonth > 12) { gridMonth = 1; gridYear++; }
            $('#mar-grid-year').val(gridYear);
            $('#mar-grid-month').val(gridMonth);
            loadMonthlyGrid();
        });

        // Click cell to administer
        $(document).on('click', '.mar-grid-cell', function() {
            var sheetId = $(this).data('sheet-id');
            var slot = $(this).data('slot');
            var date = $(this).data('date');
            var medName = $(this).data('med-name');
            var dosage = $(this).data('dosage');

            if (!sheetId || slot === '—') return;

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
        });

        // Save stock
        $(document).on('click', '.mar-stock-save-btn', function() {
            var sheetId = $(this).data('sheet-id');
            var form = $(this).closest('.mar-grid-stock-form');
            var btn = $(this);

            var data = {
                _token: csrfToken,
                id: sheetId,
                quantity_received: form.find('[data-field="quantity_received"]').val() || null,
                quantity_carried_forward: form.find('[data-field="quantity_carried_forward"]').val() || null,
                quantity_returned: form.find('[data-field="quantity_returned"]').val() || null
            };

            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: baseUrl + '/roster/client/mar-stock-update',
                type: 'POST',
                data: data,
                success: function(res) {
                    if (res.success) {
                        btn.html('<i class="fa fa-check"></i> Saved').addClass('btn-success').removeClass('btn-default');
                        setTimeout(function() {
                            btn.html('<i class="fa fa-save"></i> Save').removeClass('btn-success').addClass('btn-default').prop('disabled', false);
                            loadMonthlyGrid();
                        }, 1000);
                    } else {
                        alert(res.message || 'Could not update stock.');
                        btn.html('<i class="fa fa-save"></i> Save').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Error updating stock. Please try again.');
                    btn.html('<i class="fa fa-save"></i> Save').prop('disabled', false);
                }
            });
        });

        // Print button
        $(document).on('click', '#mar-grid-print-btn', function() {
            var url = baseUrl + '/roster/mar-print/' + gridClientId + '/' + gridYear + '/' + gridMonth;
            window.open(url, '_blank');
        });

        // Refresh grid after administration is saved (hook into existing modal save)
        var origSaveAdmin = window._marGridAdminCallback;
        $(document).on('hidden.bs.modal', '#marAdministerModal', function() {
            if ($('#mar-monthly-grid-container').is(':visible') && gridClientId) {
                loadMonthlyGrid();
            }
        });

        // Show monthly grid section
        $(document).on('click', '#viewMonthlyGridBtn', function() {
            $('.medicationSectionFirst').hide();
            $('.medicationSectionSecond').hide();
            $('.medicationSectionGrid').show();
            if (gridClientId) {
                loadMonthlyGrid();
            } else if (typeof client_id !== 'undefined') {
                initMARGrid(client_id);
            }
        });

        // Back from grid to list
        $(document).on('click', '#gridBackBtn', function() {
            $('.medicationSectionGrid').hide();
            $('.medicationSectionFirst').show();
        });
    });

})();
