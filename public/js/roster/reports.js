$(document).ready(function () {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });

    var selectedType = null;
    var reportData = [];
    var reportColumns = [];
    var sortKey = null;
    var sortAsc = true;

    function esc(str) {
        if (!str && str !== 0) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(String(str)));
        return div.innerHTML;
    }

    // Default date range: last 30 days
    var today = new Date();
    var thirtyAgo = new Date();
    thirtyAgo.setDate(today.getDate() - 30);
    $('#dateTo').val(today.toISOString().split('T')[0]);
    $('#dateFrom').val(thirtyAgo.toISOString().split('T')[0]);

    // ═══════════════════════════════════════
    //  Tab switching
    // ═══════════════════════════════════════
    $('.rpt-tab').on('click', function () {
        var tab = $(this).data('tab');
        $('.rpt-tab').removeClass('active');
        $(this).addClass('active');
        $('.rpt-tab-content').removeClass('active');
        if (tab === 'generate') {
            $('#tabGenerate').addClass('active');
        } else {
            $('#tabScheduled').addClass('active');
            loadSchedules();
        }
    });

    // ═══════════════════════════════════════
    //  Tab 1 — Generate Report (unchanged)
    // ═══════════════════════════════════════
    $('.report-card').on('click', function () {
        var type = $(this).data('type');
        if (selectedType === type) return;
        selectedType = type;
        $('.report-card').removeClass('active');
        $(this).addClass('active');
        $('#filterSection').addClass('visible');
        $('.filter-extra').removeClass('visible');
        $('.filter-extra[data-for="' + type + '"]').addClass('visible');
        hideResults();
    });

    function hideResults() {
        $('#reportSummary').removeClass('visible');
        $('#reportTableWrap').removeClass('visible');
        $('#emptyState').removeClass('visible');
        $('#loadingOverlay').removeClass('visible');
        $('#truncatedNotice').removeClass('visible');
        $('#btnExportCSV').hide();
    }

    $('#btnGenerate').on('click', function () {
        if (!selectedType) return;
        var params = {
            report_type: selectedType,
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val()
        };
        if (selectedType === 'training') params.status = $('#filterTrainingStatus').val();
        else if (selectedType === 'mar') params.code = $('#filterMARCode').val();
        else if (selectedType === 'shifts') { params.shift_type = $('#filterShiftType').val(); params.status = $('#filterShiftStatus').val(); }
        else if (selectedType === 'feedback') { params.feedback_type = $('#filterFeedbackType').val(); params.status = $('#filterFeedbackStatus').val(); }

        hideResults();
        $('#loadingOverlay').addClass('visible');

        $.ajax({
            url: baseUrl + '/roster/reports/generate',
            method: 'GET',
            data: params,
            dataType: 'json',
            success: function (resp) {
                $('#loadingOverlay').removeClass('visible');
                if (resp.status && resp.report) {
                    reportData = resp.report.data || [];
                    reportColumns = resp.report.columns || [];
                    sortKey = null; sortAsc = true;
                    renderSummary(resp.report.summary);
                    if (reportData.length > 0) {
                        renderTable();
                        $('#reportTableWrap').addClass('visible');
                        $('#btnExportCSV').show();
                        if (resp.report.summary.total > 500) {
                            $('#totalRecords').text(resp.report.summary.total);
                            $('#truncatedNotice').addClass('visible');
                        }
                    } else {
                        $('#emptyState').addClass('visible');
                    }
                }
            },
            error: function (xhr) {
                $('#loadingOverlay').removeClass('visible');
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON && xhr.responseJSON.errors;
                    var msg = 'Validation error';
                    if (errors) { var first = Object.values(errors)[0]; msg = Array.isArray(first) ? first[0] : first; }
                    alert(msg);
                } else {
                    alert('Failed to generate report. Please try again.');
                }
            }
        });
    });

    function renderSummary(summary) {
        var count = summary.total || 0;
        $('#recordCount').html('<span>' + esc(count) + '</span> records found');
        var badges = '';
        if (selectedType === 'incidents') {
            badges = '<span class="summary-badge">Total: <span class="sb-val">' + esc(summary.total) + '</span></span>';
        } else if (selectedType === 'training') {
            badges = '<span class="summary-badge">Total: <span class="sb-val">' + esc(summary.total) + '</span></span>' +
                '<span class="summary-badge">Completed: <span class="sb-val">' + esc(summary.completed) + '</span></span>' +
                '<span class="summary-badge">Pending: <span class="sb-val">' + esc(summary.pending) + '</span></span>' +
                '<span class="summary-badge">Overdue: <span class="sb-val">' + esc(summary.overdue) + '</span></span>' +
                '<span class="summary-badge">Compliance: <span class="sb-val">' + esc(summary.compliance_rate) + '%</span></span>';
        } else if (selectedType === 'mar') {
            badges = '<span class="summary-badge">Total: <span class="sb-val">' + esc(summary.total) + '</span></span>' +
                '<span class="summary-badge">Administered: <span class="sb-val">' + esc(summary.administered) + '</span></span>' +
                '<span class="summary-badge">Refused: <span class="sb-val">' + esc(summary.refused) + '</span></span>' +
                '<span class="summary-badge">Spoilt: <span class="sb-val">' + esc(summary.spoilt) + '</span></span>' +
                '<span class="summary-badge">Compliance: <span class="sb-val">' + esc(summary.compliance_rate) + '%</span></span>';
        } else if (selectedType === 'shifts') {
            badges = '<span class="summary-badge">Total: <span class="sb-val">' + esc(summary.total) + '</span></span>' +
                '<span class="summary-badge">Filled: <span class="sb-val">' + esc(summary.filled) + '</span></span>' +
                '<span class="summary-badge">Unfilled: <span class="sb-val">' + esc(summary.unfilled) + '</span></span>' +
                '<span class="summary-badge">Fill Rate: <span class="sb-val">' + esc(summary.fill_rate) + '%</span></span>';
        } else if (selectedType === 'feedback') {
            badges = '<span class="summary-badge">Total: <span class="sb-val">' + esc(summary.total) + '</span></span>' +
                '<span class="summary-badge">Avg Rating: <span class="sb-val">' + esc(summary.avg_rating) + '/5</span></span>' +
                '<span class="summary-badge">New: <span class="sb-val">' + esc(summary['new']) + '</span></span>' +
                '<span class="summary-badge">Resolved: <span class="sb-val">' + esc(summary.resolved) + '</span></span>';
        }
        $('#summaryBadges').html(badges);
        $('#reportSummary').addClass('visible');
    }

    function renderTable() {
        var thead = '<tr>';
        for (var i = 0; i < reportColumns.length; i++) {
            var col = reportColumns[i];
            var icon = '';
            if (sortKey === col.key) icon = sortAsc ? ' <i class="fa fa-sort-asc sort-icon"></i>' : ' <i class="fa fa-sort-desc sort-icon"></i>';
            else icon = ' <i class="fa fa-sort sort-icon"></i>';
            thead += '<th data-key="' + esc(col.key) + '">' + esc(col.label) + icon + '</th>';
        }
        thead += '</tr>';
        $('#reportThead').html(thead);

        var tbody = '';
        var limit = Math.min(reportData.length, 500);
        for (var j = 0; j < limit; j++) {
            var row = reportData[j];
            tbody += '<tr>';
            for (var k = 0; k < reportColumns.length; k++) {
                var key = reportColumns[k].key;
                var val = row[key];
                if (key === 'rating' && val) {
                    var stars = '';
                    for (var s = 0; s < 5; s++) stars += s < val ? '<i class="fa fa-star" style="color:#f5a623"></i>' : '<i class="fa fa-star-o" style="color:#ddd"></i>';
                    tbody += '<td>' + stars + '</td>';
                } else {
                    tbody += '<td title="' + esc(val) + '">' + esc(val) + '</td>';
                }
            }
            tbody += '</tr>';
        }
        $('#reportTbody').html(tbody);
    }

    $(document).on('click', '#reportThead th', function () {
        var key = $(this).data('key');
        if (sortKey === key) sortAsc = !sortAsc;
        else { sortKey = key; sortAsc = true; }
        reportData.sort(function (a, b) {
            var va = a[key] || '', vb = b[key] || '';
            if (typeof va === 'number' && typeof vb === 'number') return sortAsc ? va - vb : vb - va;
            va = String(va).toLowerCase(); vb = String(vb).toLowerCase();
            if (va < vb) return sortAsc ? -1 : 1;
            if (va > vb) return sortAsc ? 1 : -1;
            return 0;
        });
        renderTable();
    });

    $('#btnExportCSV').on('click', function () {
        if (!reportData.length || !reportColumns.length) return;
        var csv = reportColumns.map(function (c) { return '"' + c.label.replace(/"/g, '""') + '"'; }).join(',') + '\n';
        for (var i = 0; i < reportData.length; i++) {
            var row = reportData[i];
            var line = reportColumns.map(function (c) {
                var v = row[c.key]; if (v === null || v === undefined) v = '';
                return '"' + String(v).replace(/"/g, '""') + '"';
            }).join(',');
            csv += line + '\n';
        }
        var dateStr = new Date().toISOString().split('T')[0];
        var filename = selectedType + '_report_' + dateStr + '.csv';
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url; a.download = filename;
        document.body.appendChild(a); a.click();
        document.body.removeChild(a); URL.revokeObjectURL(url);
    });

    // ═══════════════════════════════════════
    //  Tab 2 — Scheduled Reports
    // ═══════════════════════════════════════
    var schedules = [];

    var typeLabels = {
        incidents: 'Incident Summary',
        training: 'Training Compliance',
        mar: 'MAR Compliance',
        shifts: 'Shift Coverage',
        feedback: 'Client Feedback'
    };

    var freqLabels = {
        daily: 'Daily',
        weekly: 'Weekly',
        fortnightly: 'Fortnightly',
        monthly: 'Monthly'
    };

    var dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    function loadSchedules() {
        $.ajax({
            url: baseUrl + '/roster/reports/schedules',
            method: 'GET',
            dataType: 'json',
            success: function (resp) {
                if (resp.status) {
                    schedules = resp.schedules || [];
                    renderSchedules();
                }
            },
            error: function () {
                alert('Failed to load schedules.');
            }
        });
    }

    function renderSchedules() {
        var activeCount = 0;
        var sentThisMonth = 0;
        var now = new Date();
        var thisMonth = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');

        var html = '';
        for (var i = 0; i < schedules.length; i++) {
            var s = schedules[i];
            if (s.is_active) activeCount++;
            if (s.last_run_date && s.last_run_status === 'success' && s.last_run_date.substring(0, 7) === thisMonth) sentThisMonth++;

            var inactiveClass = s.is_active ? '' : ' inactive';
            var toggleIcon = s.is_active ? 'fa-pause' : 'fa-play';
            var toggleTitle = s.is_active ? 'Pause' : 'Resume';

            var recipients = [];
            try { recipients = typeof s.recipients === 'string' ? JSON.parse(s.recipients) : s.recipients; } catch(e) { recipients = []; }
            var recipientCount = recipients.length;

            var nextRunStr = '—';
            if (s.next_run_date && s.is_active) {
                var d = new Date(s.next_run_date);
                nextRunStr = dayNames[d.getDay()] + ', ' + d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) + ' at ' + String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
            }

            html += '<div class="sched-card' + inactiveClass + '" data-id="' + esc(s.id) + '">';
            html += '<div class="sched-card-info">';
            html += '<div class="sched-card-name">' + esc(s.report_name) + '</div>';
            html += '<div class="sched-card-meta">';
            html += '<span class="sched-badge sched-badge-type">' + esc(typeLabels[s.report_type] || s.report_type) + '</span>';
            html += '<span class="sched-badge sched-badge-freq">' + esc(freqLabels[s.schedule_frequency] || s.schedule_frequency) + '</span>';
            html += '<span><i class="fa fa-envelope"></i> ' + esc(recipientCount) + ' recipient' + (recipientCount !== 1 ? 's' : '') + '</span>';
            if (!s.is_active) {
                html += '<span class="sched-badge sched-badge-paused">Paused</span>';
            } else {
                html += '<span><i class="fa fa-clock-o"></i> Next: ' + esc(nextRunStr) + '</span>';
            }
            html += '</div></div>';
            html += '<div class="sched-card-actions">';
            html += '<button class="btn-edit" title="Edit" data-id="' + esc(s.id) + '"><i class="fa fa-pencil"></i></button>';
            html += '<button class="btn-toggle" title="' + esc(toggleTitle) + '" data-id="' + esc(s.id) + '"><i class="fa ' + toggleIcon + '"></i></button>';
            html += '<button class="btn-delete" title="Delete" data-id="' + esc(s.id) + '"><i class="fa fa-trash"></i></button>';
            html += '</div></div>';
        }

        $('#schedList').html(html);
        $('#schedActiveCount').text(activeCount);
        $('#schedSentCount').text(sentThisMonth);

        if (schedules.length === 0) {
            $('#schedEmpty').show();
        } else {
            $('#schedEmpty').hide();
        }
    }

    // New schedule
    $('#btnNewSchedule').on('click', function () {
        $('#schedEditId').val('');
        $('#schedModalTitle').text('New Schedule');
        $('#schedName').val('');
        $('#schedType').val('incidents');
        $('#schedFrequency').val('weekly');
        $('#schedDayOfWeek').val('1');
        $('#schedDayOfMonth').val('1');
        $('#schedTime').val('08:00');
        $('#schedRecipients').val('');
        $('#schedFormat').val('csv');
        $('#schedActive').prop('checked', true);
        $('#schedNotes').val('');
        updateDayVisibility();
        updateNextRunPreview();
        $('#scheduleModal').modal('show');
    });

    // Edit schedule
    $(document).on('click', '.btn-edit', function () {
        var id = $(this).data('id');
        var s = null;
        for (var i = 0; i < schedules.length; i++) {
            if (schedules[i].id == id) { s = schedules[i]; break; }
        }
        if (!s) return;

        $('#schedEditId').val(s.id);
        $('#schedModalTitle').text('Edit Schedule');
        $('#schedName').val(s.report_name);
        $('#schedType').val(s.report_type);
        $('#schedFrequency').val(s.schedule_frequency);
        $('#schedTime').val(s.schedule_time);
        $('#schedFormat').val(s.output_format);
        $('#schedActive').prop('checked', s.is_active);
        $('#schedNotes').val(s.notes || '');

        var recipients = [];
        try { recipients = typeof s.recipients === 'string' ? JSON.parse(s.recipients) : s.recipients; } catch(e) { recipients = []; }
        $('#schedRecipients').val(recipients.join(', '));

        if (s.schedule_frequency === 'monthly') {
            $('#schedDayOfMonth').val(s.schedule_day || 1);
        } else {
            $('#schedDayOfWeek').val(s.schedule_day !== null ? s.schedule_day : 1);
        }

        updateDayVisibility();
        updateNextRunPreview();
        $('#scheduleModal').modal('show');
    });

    // Toggle active
    $(document).on('click', '.btn-toggle', function () {
        var id = $(this).data('id');
        $.ajax({
            url: baseUrl + '/roster/reports/schedule/toggle',
            method: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function (resp) {
                if (resp.status) loadSchedules();
                else alert(resp.message || 'Failed to toggle schedule.');
            },
            error: function () { alert('Failed to toggle schedule.'); }
        });
    });

    // Delete schedule
    $(document).on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        if (!confirm('Delete this scheduled report? This cannot be undone.')) return;
        $.ajax({
            url: baseUrl + '/roster/reports/schedule/delete',
            method: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function (resp) {
                if (resp.status) loadSchedules();
                else alert(resp.message || 'Failed to delete schedule.');
            },
            error: function () { alert('Failed to delete schedule.'); }
        });
    });

    // Save schedule (create or update)
    $('#btnSaveSchedule').on('click', function () {
        var editId = $('#schedEditId').val();
        var freq = $('#schedFrequency').val();
        var day = null;
        if (freq === 'monthly') day = $('#schedDayOfMonth').val();
        else if (freq === 'weekly' || freq === 'fortnightly') day = $('#schedDayOfWeek').val();

        var data = {
            report_name: $('#schedName').val(),
            report_type: $('#schedType').val(),
            schedule_frequency: freq,
            schedule_day: day,
            schedule_time: $('#schedTime').val(),
            recipients: $('#schedRecipients').val(),
            output_format: $('#schedFormat').val(),
            is_active: $('#schedActive').is(':checked') ? 1 : 0,
            notes: $('#schedNotes').val()
        };

        var url, isEdit = false;
        if (editId) {
            url = baseUrl + '/roster/reports/schedule/update';
            data.id = editId;
            isEdit = true;
        } else {
            url = baseUrl + '/roster/reports/schedule/store';
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            dataType: 'json',
            success: function (resp) {
                if (resp.status) {
                    $('#scheduleModal').modal('hide');
                    loadSchedules();
                } else {
                    alert(resp.message || 'Failed to save schedule.');
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var resp = xhr.responseJSON;
                    if (resp && resp.errors) {
                        var msgs = [];
                        $.each(resp.errors, function (k, v) { msgs.push(Array.isArray(v) ? v[0] : v); });
                        alert(msgs.join('\n'));
                    } else if (resp && resp.message) {
                        alert(resp.message);
                    } else {
                        alert('Validation error. Please check your input.');
                    }
                } else {
                    alert('Failed to save schedule.');
                }
            }
        });
    });

    // Frequency change → show/hide day fields
    $('#schedFrequency').on('change', function () {
        updateDayVisibility();
        updateNextRunPreview();
    });
    $('#schedDayOfWeek, #schedDayOfMonth, #schedTime').on('change', updateNextRunPreview);

    function updateDayVisibility() {
        var freq = $('#schedFrequency').val();
        if (freq === 'monthly') {
            $('#schedDayOfWeekWrap').hide();
            $('#schedDayOfMonthWrap').show();
        } else if (freq === 'weekly' || freq === 'fortnightly') {
            $('#schedDayOfWeekWrap').show();
            $('#schedDayOfMonthWrap').hide();
        } else {
            $('#schedDayOfWeekWrap').hide();
            $('#schedDayOfMonthWrap').hide();
        }
    }

    function updateNextRunPreview() {
        var freq = $('#schedFrequency').val();
        var time = $('#schedTime').val() || '08:00';
        var parts = time.split(':');
        var hour = parseInt(parts[0]) || 8;
        var minute = parseInt(parts[1]) || 0;
        var now = new Date();
        var next = null;

        if (freq === 'daily') {
            next = new Date(now.getFullYear(), now.getMonth(), now.getDate(), hour, minute, 0);
            if (next <= now) next.setDate(next.getDate() + 1);
        } else if (freq === 'weekly' || freq === 'fortnightly') {
            var targetDay = parseInt($('#schedDayOfWeek').val()) || 1;
            next = new Date(now);
            var diff = targetDay - now.getDay();
            if (diff < 0) diff += 7;
            if (diff === 0) {
                var todayCheck = new Date(now.getFullYear(), now.getMonth(), now.getDate(), hour, minute, 0);
                if (todayCheck > now) {
                    next = todayCheck;
                } else {
                    next.setDate(now.getDate() + 7);
                    next = new Date(next.getFullYear(), next.getMonth(), next.getDate(), hour, minute, 0);
                }
            } else {
                next.setDate(now.getDate() + diff);
                next = new Date(next.getFullYear(), next.getMonth(), next.getDate(), hour, minute, 0);
            }
            if (freq === 'fortnightly') next.setDate(next.getDate() + 7);
        } else if (freq === 'monthly') {
            var dayOfMonth = parseInt($('#schedDayOfMonth').val()) || 1;
            next = new Date(now.getFullYear(), now.getMonth(), dayOfMonth, hour, minute, 0);
            if (next <= now) next.setMonth(next.getMonth() + 1);
        }

        if (next) {
            var str = dayNames[next.getDay()] + ', ' + next.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }) + ' at ' + String(next.getHours()).padStart(2, '0') + ':' + String(next.getMinutes()).padStart(2, '0');
            $('#schedNextRunText').text(str);
        } else {
            $('#schedNextRunText').text('—');
        }
    }
});
