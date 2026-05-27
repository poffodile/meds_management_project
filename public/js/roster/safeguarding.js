$(document).ready(function() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val();
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': csrfToken} });

    var currentPage = 1;

    function esc(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    var riskColors = {
        low: 'sg-badge-low',
        medium: 'sg-badge-medium',
        high: 'sg-badge-high',
        critical: 'sg-badge-critical'
    };

    var statusColors = {
        reported: 'sg-badge-reported',
        under_investigation: 'sg-badge-under_investigation',
        safeguarding_plan: 'sg-badge-safeguarding_plan',
        closed: 'sg-badge-closed'
    };

    var statusLabels = {
        reported: 'Reported',
        under_investigation: 'Under Investigation',
        safeguarding_plan: 'Safeguarding Plan',
        closed: 'Closed'
    };

    var riskLabels = {
        low: 'Low',
        medium: 'Medium',
        high: 'High',
        critical: 'Critical'
    };

    var nextStatus = {
        reported: 'under_investigation',
        under_investigation: 'safeguarding_plan',
        safeguarding_plan: 'closed'
    };

    var statusActions = {
        reported: 'Start Investigation',
        under_investigation: 'Create Safeguarding Plan',
        safeguarding_plan: 'Close Case'
    };

    var outcomeLabels = {
        substantiated: 'Substantiated',
        partially_substantiated: 'Partially Substantiated',
        unsubstantiated: 'Unsubstantiated',
        inconclusive: 'Inconclusive'
    };

    function loadReferrals(page) {
        var params = { page: page || 1 };
        var status = $('#filter-status').val();
        var risk = $('#filter-risk').val();
        var search = $('#filter-search').val();
        if (status) params.status = status;
        if (risk) params.risk_level = risk;
        if (search) params.search = search;

        $('#sg-list').html('<div class="text-center" style="padding:40px;"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');

        $.ajax({
            url: baseUrl + '/roster/safeguarding/list',
            type: 'POST',
            data: params,
            success: function(res) {
                if (res.success) {
                    renderList(res.data.data, res.data);
                } else {
                    $('#sg-list').html('<div class="sg-empty"><i class="fa fa-exclamation-circle"></i>Failed to load referrals.</div>');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON && xhr.responseJSON.errors;
                    var msg = errors ? Object.values(errors).flat().join(', ') : 'Validation error.';
                    $('#sg-list').html('<div class="sg-empty"><i class="fa fa-exclamation-circle"></i>' + esc(msg) + '</div>');
                } else {
                    $('#sg-list').html('<div class="sg-empty"><i class="fa fa-exclamation-circle"></i>Failed to load referrals.</div>');
                }
            }
        });
    }

    function renderList(referrals, pagination) {
        if (!referrals || referrals.length === 0) {
            $('#sg-list').html('<div class="sg-empty"><i class="fa fa-shield"></i>No safeguarding referrals found.</div>');
            $('#sg-pagination').html('');
            return;
        }

        var html = '';
        for (var i = 0; i < referrals.length; i++) {
            var r = referrals[i];
            var types = r.safeguarding_type || [];
            var typeTags = '';
            for (var t = 0; t < types.length; t++) {
                typeTags += '<span class="sg-type-tag">' + esc(types[t]) + '</span>';
            }

            var reportedBy = 'Unknown';
            if (r.reported_by_user) {
                reportedBy = esc(r.reported_by_user.name || 'Unknown');
            }

            var dateStr = r.date_of_concern ? new Date(r.date_of_concern).toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'}) : '';

            html += '<div class="sg-card" data-id="' + r.id + '">';
            html += '<div style="display:flex;justify-content:space-between;align-items:center;">';
            html += '<span class="sg-ref">' + esc(r.reference_number) + '</span>';
            html += '<div>';
            html += '<span class="sg-badge ' + (riskColors[r.risk_level] || '') + '">' + esc(riskLabels[r.risk_level] || r.risk_level) + '</span> ';
            html += '<span class="sg-badge ' + (statusColors[r.status] || '') + '">' + esc(statusLabels[r.status] || r.status) + '</span>';
            if (r.ongoing_risk) html += ' <span class="sg-ongoing">Ongoing Risk</span>';
            html += '</div></div>';
            html += '<div class="sg-meta">' + esc(dateStr) + ' &mdash; Reported by: ' + reportedBy + '</div>';
            html += '<div class="sg-type-tags">' + typeTags + '</div>';
            html += '<div style="margin-top:6px;font-size:13px;color:#555;">' + esc((r.details_of_concern || '').substring(0, 150)) + (r.details_of_concern && r.details_of_concern.length > 150 ? '...' : '') + '</div>';
            html += '</div>';
        }
        $('#sg-list').html(html);

        // Pagination
        if (pagination.last_page > 1) {
            var pagHtml = '';
            for (var p = 1; p <= pagination.last_page; p++) {
                pagHtml += '<button class="btn btn-sm ' + (p === pagination.current_page ? 'btn-primary' : 'btn-default') + ' sg-page-btn" data-page="' + p + '">' + p + '</button> ';
            }
            $('#sg-pagination').html(pagHtml);
        } else {
            $('#sg-pagination').html('');
        }
    }

    // Click referral card → open detail
    $(document).on('click', '.sg-card', function() {
        var id = $(this).data('id');
        loadDetails(id);
    });

    // Pagination
    $(document).on('click', '.sg-page-btn', function() {
        currentPage = $(this).data('page');
        loadReferrals(currentPage);
    });

    function loadDetails(id) {
        $.ajax({
            url: baseUrl + '/roster/safeguarding/details',
            type: 'POST',
            data: { id: id },
            success: function(res) {
                if (res.success) {
                    renderDetail(res.data);
                    $('#sg-detail-modal').modal('show');
                } else {
                    alert('Referral not found.');
                }
            },
            error: function() {
                alert('Failed to load referral details.');
            }
        });
    }

    function renderDetail(r) {
        var html = '';

        // Header
        html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">';
        html += '<h4 style="margin:0;">' + esc(r.reference_number) + '</h4>';
        html += '<div>';
        html += '<span class="sg-badge ' + (riskColors[r.risk_level] || '') + '">' + esc(riskLabels[r.risk_level] || '') + ' Risk</span> ';
        html += '<span class="sg-badge ' + (statusColors[r.status] || '') + '">' + esc(statusLabels[r.status] || '') + '</span>';
        if (r.ongoing_risk) html += ' <span class="sg-ongoing">Ongoing Risk</span>';
        html += '</div></div>';

        // Concern Details
        html += '<div class="sg-detail-section">';
        html += '<h5>Concern Details</h5>';
        html += detailRow('Date of Concern', r.date_of_concern ? new Date(r.date_of_concern).toLocaleString('en-GB') : '-');
        html += detailRow('Location', r.location_of_incident || '-');
        var reportedBy = 'Unknown';
        if (r.reported_by_user) reportedBy = r.reported_by_user.name || 'Unknown';
        html += detailRow('Reported By', reportedBy);
        html += detailRow('Details', r.details_of_concern || '-');
        html += detailRow('Immediate Action', r.immediate_action_taken || '-');
        html += '</div>';

        // Classification
        html += '<div class="sg-detail-section">';
        html += '<h5>Classification</h5>';
        var types = r.safeguarding_type || [];
        var typeStr = '';
        for (var i = 0; i < types.length; i++) typeStr += '<span class="sg-type-tag">' + esc(types[i]) + '</span>';
        html += '<div class="sg-detail-row"><div class="sg-detail-label">Type(s)</div><div class="sg-detail-value">' + typeStr + '</div></div>';
        html += detailRow('Risk Level', riskLabels[r.risk_level] || r.risk_level);
        html += detailRow('Ongoing Risk', r.ongoing_risk ? 'Yes' : 'No');
        html += '</div>';

        // People Involved
        html += '<div class="sg-detail-section">';
        html += '<h5>People Involved</h5>';
        if (r.alleged_perpetrator) {
            var perp = r.alleged_perpetrator;
            html += detailRow('Alleged Perpetrator', (perp.name || '-') + ' (' + (perp.relationship || '-') + ')');
            if (perp.details) html += detailRow('Perpetrator Details', perp.details);
        } else {
            html += detailRow('Alleged Perpetrator', 'Not recorded');
        }
        html += detailRow('Capacity to Decide', r.capacity_to_make_decisions === true ? 'Yes' : (r.capacity_to_make_decisions === false ? 'No' : 'Unknown'));
        html += detailRow('Client Wishes', r.client_wishes || '-');

        if (r.witnesses && r.witnesses.length > 0) {
            html += '<div style="margin-top:10px;"><strong style="font-size:13px;">Witnesses:</strong></div>';
            for (var w = 0; w < r.witnesses.length; w++) {
                var wit = r.witnesses[w];
                html += '<div style="background:#f9f9fb;border:1px solid #eee;border-radius:4px;padding:8px;margin-top:6px;">';
                html += '<strong>' + esc(wit.name || 'Unknown') + '</strong>';
                if (wit.role) html += ' <span style="color:#888;">(' + esc(wit.role) + ')</span>';
                if (wit.statement) html += '<div style="margin-top:4px;font-size:13px;color:#555;">' + esc(wit.statement) + '</div>';
                html += '</div>';
            }
        }
        html += '</div>';

        // Multi-Agency Notifications
        html += '<div class="sg-detail-section">';
        html += '<h5>Multi-Agency Notifications</h5>';
        html += detailRow('Police', r.police_notified ? 'Yes' + (r.police_reference ? ' (Ref: ' + r.police_reference + ')' : '') : 'No');
        if (r.police_notification_date) html += detailRow('Police Notified', new Date(r.police_notification_date).toLocaleString('en-GB'));
        html += detailRow('Local Authority', r.local_authority_notified ? 'Yes' + (r.local_authority_reference ? ' (Ref: ' + r.local_authority_reference + ')' : '') : 'No');
        if (r.local_authority_notification_date) html += detailRow('LA Notified', new Date(r.local_authority_notification_date).toLocaleString('en-GB'));
        html += detailRow('CQC', r.cqc_notified ? 'Yes' : 'No');
        if (r.cqc_notification_date) html += detailRow('CQC Notified', new Date(r.cqc_notification_date).toLocaleString('en-GB'));
        html += detailRow('Family', r.family_notified ? 'Yes' : 'No');
        if (r.family_notification_details) html += detailRow('Family Details', r.family_notification_details);
        html += detailRow('Advocate', r.advocate_involved ? 'Yes' : 'No');
        if (r.advocate_details) html += detailRow('Advocate Details', r.advocate_details);
        html += '</div>';

        // Strategy Meeting
        if (r.strategy_meeting) {
            html += '<div class="sg-detail-section">';
            html += '<h5>Strategy Meeting</h5>';
            html += detailRow('Required', r.strategy_meeting.required ? 'Yes' : 'No');
            if (r.strategy_meeting.date) html += detailRow('Date', new Date(r.strategy_meeting.date).toLocaleString('en-GB'));
            if (r.strategy_meeting.outcome) html += detailRow('Outcome', r.strategy_meeting.outcome);
            html += '</div>';
        }

        // Safeguarding Plan
        if (r.safeguarding_plan) {
            html += '<div class="sg-detail-section">';
            html += '<h5>Safeguarding Plan</h5>';
            var plan = r.safeguarding_plan;
            if (plan.agreed_actions && plan.agreed_actions.length > 0) {
                var actionsHtml = '<ul style="margin:0;padding-left:20px;">';
                for (var a = 0; a < plan.agreed_actions.length; a++) actionsHtml += '<li>' + esc(plan.agreed_actions[a]) + '</li>';
                actionsHtml += '</ul>';
                html += '<div class="sg-detail-row"><div class="sg-detail-label">Agreed Actions</div><div class="sg-detail-value">' + actionsHtml + '</div></div>';
            }
            if (plan.responsible_persons && plan.responsible_persons.length > 0) {
                html += detailRow('Responsible', plan.responsible_persons.join(', '));
            }
            if (plan.timescales) html += detailRow('Timescales', plan.timescales);
            if (plan.monitoring_arrangements) html += detailRow('Monitoring', plan.monitoring_arrangements);
            html += '</div>';
        }

        // Outcome
        if (r.outcome || r.outcome_details || r.lessons_learned) {
            html += '<div class="sg-detail-section">';
            html += '<h5>Outcome</h5>';
            if (r.outcome) html += detailRow('Outcome', outcomeLabels[r.outcome] || r.outcome);
            if (r.outcome_details) html += detailRow('Details', r.outcome_details);
            if (r.lessons_learned) html += detailRow('Lessons Learned', r.lessons_learned);
            if (r.closed_date) html += detailRow('Closed Date', new Date(r.closed_date).toLocaleString('en-GB'));
            html += '</div>';
        }

        $('#sg-detail-body').html(html);

        // Footer buttons
        var footerHtml = '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
        if (r.status !== 'closed') {
            footerHtml += ' <button type="button" class="btn btn-primary sg-edit-btn" data-id="' + r.id + '"><i class="fa fa-pencil"></i> Edit</button>';
            if (nextStatus[r.status]) {
                footerHtml += ' <button type="button" class="btn btn-warning sg-status-btn" data-id="' + r.id + '" data-status="' + esc(nextStatus[r.status]) + '"><i class="fa fa-arrow-right"></i> ' + esc(statusActions[r.status]) + '</button>';
            }
        }
        footerHtml += ' <button type="button" class="btn btn-danger sg-delete-btn" data-id="' + r.id + '"><i class="fa fa-trash"></i> Delete</button>';
        $('#sg-detail-footer').html(footerHtml);

        // Store current referral for edit
        $('#sg-detail-modal').data('referral', r);
    }

    function detailRow(label, value) {
        return '<div class="sg-detail-row"><div class="sg-detail-label">' + esc(label) + '</div><div class="sg-detail-value">' + esc(value) + '</div></div>';
    }

    // Notification toggles
    $('#sg-police-notified').change(function() {
        $('#sg-police-ref, #sg-police-date').toggle(this.checked);
    });
    $('#sg-la-notified').change(function() {
        $('#sg-la-ref, #sg-la-date').toggle(this.checked);
    });
    $('#sg-cqc-notified').change(function() {
        $('#sg-cqc-date').toggle(this.checked);
    });
    $('#sg-family-notified').change(function() {
        $('#sg-family-details').toggle(this.checked);
    });
    $('#sg-advocate-involved').change(function() {
        $('#sg-advocate-details').toggle(this.checked);
    });

    // Add witness
    var witnessCount = 0;
    $('#btn-add-witness').click(function() {
        witnessCount++;
        var html = '<div class="sg-witness-row" id="witness-' + witnessCount + '">';
        html += '<div class="row">';
        html += '<div class="col-md-3"><input type="text" class="form-control witness-name" placeholder="Name" maxlength="200"></div>';
        html += '<div class="col-md-3"><input type="text" class="form-control witness-role" placeholder="Role" maxlength="200"></div>';
        html += '<div class="col-md-5"><input type="text" class="form-control witness-statement" placeholder="Statement" maxlength="2000"></div>';
        html += '<div class="col-md-1"><button type="button" class="btn btn-sm btn-danger remove-witness" data-id="' + witnessCount + '"><i class="fa fa-times"></i></button></div>';
        html += '</div></div>';
        $('#sg-witnesses-container').append(html);
    });

    $(document).on('click', '.remove-witness', function() {
        $('#witness-' + $(this).data('id')).remove();
    });

    // New referral
    $('#btn-new-referral').click(function() {
        resetForm();
        $('#sg-form-title').text('Raise Safeguarding Concern');
        $('#sg-form-modal').modal('show');
    });

    function resetForm() {
        $('#sg-edit-id').val('');
        $('#sg-form')[0].reset();
        $('#sg-witnesses-container').html('');
        witnessCount = 0;
        $('#sg-police-ref, #sg-police-date, #sg-la-ref, #sg-la-date, #sg-cqc-date, #sg-family-details, #sg-advocate-details').hide();
        $('input[name="safeguarding_type[]"]').prop('checked', false);
    }

    // Edit
    $(document).on('click', '.sg-edit-btn', function() {
        var r = $('#sg-detail-modal').data('referral');
        if (!r) return;
        $('#sg-detail-modal').modal('hide');
        populateForm(r);
        $('#sg-form-title').text('Edit Referral — ' + (r.reference_number || ''));
        $('#sg-form-modal').modal('show');
    });

    function populateForm(r) {
        resetForm();
        $('#sg-edit-id').val(r.id);
        if (r.date_of_concern) {
            var d = new Date(r.date_of_concern);
            var dtLocal = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
            $('#sg-date-of-concern').val(dtLocal);
        }
        $('#sg-location').val(r.location_of_incident || '');
        $('#sg-details').val(r.details_of_concern || '');
        $('#sg-immediate-action').val(r.immediate_action_taken || '');
        $('#sg-risk-level').val(r.risk_level || '');
        $('#sg-ongoing-risk').val(r.ongoing_risk ? '1' : '0');

        var types = r.safeguarding_type || [];
        $('input[name="safeguarding_type[]"]').each(function() {
            $(this).prop('checked', types.indexOf($(this).val()) !== -1);
        });

        if (r.alleged_perpetrator) {
            $('#sg-perp-name').val(r.alleged_perpetrator.name || '');
            $('#sg-perp-relationship').val(r.alleged_perpetrator.relationship || '');
            $('#sg-perp-details').val(r.alleged_perpetrator.details || '');
        }

        if (r.witnesses && r.witnesses.length > 0) {
            for (var i = 0; i < r.witnesses.length; i++) {
                $('#btn-add-witness').click();
                var row = $('#sg-witnesses-container .sg-witness-row').last();
                row.find('.witness-name').val(r.witnesses[i].name || '');
                row.find('.witness-role').val(r.witnesses[i].role || '');
                row.find('.witness-statement').val(r.witnesses[i].statement || '');
            }
        }

        $('#sg-capacity').val(r.capacity_to_make_decisions === true ? '1' : (r.capacity_to_make_decisions === false ? '0' : ''));
        $('#sg-client-wishes').val(r.client_wishes || '');

        if (r.police_notified) {
            $('#sg-police-notified').prop('checked', true).trigger('change');
            $('#sg-police-ref').val(r.police_reference || '');
            if (r.police_notification_date) {
                var pd = new Date(r.police_notification_date);
                $('#sg-police-date').val(pd.getFullYear() + '-' + pad(pd.getMonth()+1) + '-' + pad(pd.getDate()) + 'T' + pad(pd.getHours()) + ':' + pad(pd.getMinutes()));
            }
        }
        if (r.local_authority_notified) {
            $('#sg-la-notified').prop('checked', true).trigger('change');
            $('#sg-la-ref').val(r.local_authority_reference || '');
            if (r.local_authority_notification_date) {
                var ld = new Date(r.local_authority_notification_date);
                $('#sg-la-date').val(ld.getFullYear() + '-' + pad(ld.getMonth()+1) + '-' + pad(ld.getDate()) + 'T' + pad(ld.getHours()) + ':' + pad(ld.getMinutes()));
            }
        }
        if (r.cqc_notified) {
            $('#sg-cqc-notified').prop('checked', true).trigger('change');
            if (r.cqc_notification_date) {
                var cd = new Date(r.cqc_notification_date);
                $('#sg-cqc-date').val(cd.getFullYear() + '-' + pad(cd.getMonth()+1) + '-' + pad(cd.getDate()) + 'T' + pad(cd.getHours()) + ':' + pad(cd.getMinutes()));
            }
        }
        if (r.family_notified) {
            $('#sg-family-notified').prop('checked', true).trigger('change');
            $('#sg-family-details').val(r.family_notification_details || '');
        }
        if (r.advocate_involved) {
            $('#sg-advocate-involved').prop('checked', true).trigger('change');
            $('#sg-advocate-details').val(r.advocate_details || '');
        }
    }

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    // Save referral
    $('#btn-save-referral').click(function() {
        var editId = $('#sg-edit-id').val();
        var selectedTypes = [];
        $('input[name="safeguarding_type[]"]:checked').each(function() {
            selectedTypes.push($(this).val());
        });

        if (!$('#sg-date-of-concern').val()) { alert('Date of concern is required.'); return; }
        if (!$('#sg-details').val().trim()) { alert('Details of concern is required.'); return; }
        if (selectedTypes.length === 0) { alert('Please select at least one safeguarding type.'); return; }
        if (!$('#sg-risk-level').val()) { alert('Risk level is required.'); return; }

        var data = {
            date_of_concern: $('#sg-date-of-concern').val(),
            location_of_incident: $('#sg-location').val(),
            details_of_concern: $('#sg-details').val(),
            immediate_action_taken: $('#sg-immediate-action').val(),
            safeguarding_type: selectedTypes,
            risk_level: $('#sg-risk-level').val(),
            ongoing_risk: parseInt($('#sg-ongoing-risk').val()),
            capacity_to_make_decisions: $('#sg-capacity').val() !== '' ? parseInt($('#sg-capacity').val()) : null,
            client_wishes: $('#sg-client-wishes').val(),
            police_notified: $('#sg-police-notified').is(':checked') ? 1 : 0,
            police_reference: $('#sg-police-ref').val(),
            police_notification_date: $('#sg-police-date').val() || null,
            local_authority_notified: $('#sg-la-notified').is(':checked') ? 1 : 0,
            local_authority_reference: $('#sg-la-ref').val(),
            local_authority_notification_date: $('#sg-la-date').val() || null,
            cqc_notified: $('#sg-cqc-notified').is(':checked') ? 1 : 0,
            cqc_notification_date: $('#sg-cqc-date').val() || null,
            family_notified: $('#sg-family-notified').is(':checked') ? 1 : 0,
            family_notification_details: $('#sg-family-details').val(),
            advocate_involved: $('#sg-advocate-involved').is(':checked') ? 1 : 0,
            advocate_details: $('#sg-advocate-details').val()
        };

        // Alleged perpetrator
        var perpName = $('#sg-perp-name').val();
        if (perpName) {
            data.alleged_perpetrator = {
                name: perpName,
                relationship: $('#sg-perp-relationship').val(),
                details: $('#sg-perp-details').val()
            };
        }

        // Witnesses
        var witnesses = [];
        $('#sg-witnesses-container .sg-witness-row').each(function() {
            var name = $(this).find('.witness-name').val();
            if (name) {
                witnesses.push({
                    name: name,
                    role: $(this).find('.witness-role').val(),
                    statement: $(this).find('.witness-statement').val()
                });
            }
        });
        if (witnesses.length > 0) data.witnesses = witnesses;

        var url = baseUrl + '/roster/safeguarding/save';
        if (editId) {
            data.id = editId;
            url = baseUrl + '/roster/safeguarding/update';
        }

        var $btn = $('#btn-save-referral');
        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: url,
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Referral');
                if (res.success) {
                    $('#sg-form-modal').modal('hide');
                    resetForm();
                    loadReferrals(currentPage);
                    alert(editId ? 'Referral updated successfully.' : 'Safeguarding concern raised successfully.');
                } else {
                    alert(res.message || 'Failed to save referral.');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Referral');
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var msgs = [];
                    for (var key in errors) {
                        if (errors.hasOwnProperty(key)) msgs.push(errors[key].join(', '));
                    }
                    alert('Validation errors:\n' + msgs.join('\n'));
                } else {
                    alert('Something went wrong. Please try again.');
                }
            }
        });
    });

    // Status change
    $(document).on('click', '.sg-status-btn', function() {
        var id = $(this).data('id');
        var newStatus = $(this).data('status');
        var label = statusLabels[newStatus] || newStatus;
        if (!confirm('Change status to "' + label + '"?')) return;

        $.ajax({
            url: baseUrl + '/roster/safeguarding/status-change',
            type: 'POST',
            data: { id: id, status: newStatus },
            success: function(res) {
                if (res.success) {
                    $('#sg-detail-modal').modal('hide');
                    loadReferrals(currentPage);
                } else {
                    alert(res.message || 'Failed to change status.');
                }
            },
            error: function() {
                alert('Something went wrong. Please try again.');
            }
        });
    });

    // Delete
    $(document).on('click', '.sg-delete-btn', function() {
        var id = $(this).data('id');
        if (!confirm('Are you sure you want to delete this referral? This action cannot be undone.')) return;

        $.ajax({
            url: baseUrl + '/roster/safeguarding/delete',
            type: 'POST',
            data: { id: id },
            success: function(res) {
                if (res.success) {
                    $('#sg-detail-modal').modal('hide');
                    loadReferrals(currentPage);
                } else {
                    alert(res.message || 'Failed to delete referral.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    alert('Only administrators can delete referrals.');
                } else {
                    alert('Something went wrong. Please try again.');
                }
            }
        });
    });

    // Filters
    $('#btn-filter').click(function() {
        currentPage = 1;
        loadReferrals(1);
    });
    $('#btn-clear-filter').click(function() {
        $('#filter-status').val('');
        $('#filter-risk').val('');
        $('#filter-search').val('');
        currentPage = 1;
        loadReferrals(1);
    });

    // Determine baseUrl
    var baseUrl = '';
    var urlMeta = $('meta[name="base-url"]').attr('content');
    if (urlMeta) {
        baseUrl = urlMeta.replace(/\/$/, '');
    } else {
        var loc = window.location;
        baseUrl = loc.protocol + '//' + loc.host;
    }

    // Initial load
    loadReferrals(1);
});
