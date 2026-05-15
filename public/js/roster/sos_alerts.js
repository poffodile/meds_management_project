function esc(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

$(document).ready(function () {
    var csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val();
    var userType = null;

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken }
    });

    // Determine user type from a hidden field or meta (we'll set it server-side)
    userType = $('#sos-user-type').val() || 'N';

    // Load alerts on page load
    loadSosAlerts();

    // SOS Trigger button opens modal
    $('#sos-trigger-btn').on('click', function () {
        $('#sos-message').val('');
        $('#sosModal').modal('show');
    });

    // Confirm SOS trigger
    $('#sos-confirm-btn').on('click', function () {
        var btn = $(this);
        btn.prop('disabled', true).text('Sending...');

        $.ajax({
            url: baseUrl + '/roster/sos-alert/trigger',
            type: 'POST',
            data: {
                message: $('#sos-message').val()
            },
            success: function (resp) {
                if (resp.success) {
                    alert('SOS Alert sent! All managers have been notified.');
                    $('#sosModal').modal('hide');
                    loadSosAlerts();
                } else {
                    alert(resp.message || 'Failed to send SOS alert.');
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON && xhr.responseJSON.errors;
                    if (errors) {
                        var msg = '';
                        for (var key in errors) {
                            msg += errors[key].join(', ') + '\n';
                        }
                        alert(msg);
                    } else {
                        alert('Validation error. Please check your input.');
                    }
                } else if (xhr.status === 429) {
                    alert('Too many requests. Please wait a moment before trying again.');
                } else {
                    alert('Something went wrong. Please try again.');
                }
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="fa fa-exclamation-triangle"></i> SEND SOS');
            }
        });
    });

    // Acknowledge alert
    $(document).on('click', '.sos-ack-btn', function () {
        var alertId = $(this).data('id');
        if (!confirm('Acknowledge this SOS alert?')) return;

        $.ajax({
            url: baseUrl + '/roster/sos-alert/acknowledge',
            type: 'POST',
            data: { id: alertId },
            success: function (resp) {
                if (resp.success) {
                    loadSosAlerts();
                } else {
                    alert(resp.message || 'Failed to acknowledge alert.');
                }
            },
            error: function (xhr) {
                if (xhr.status === 403) {
                    alert('Only managers and admins can acknowledge alerts.');
                } else if (xhr.status === 429) {
                    alert('Too many requests. Please wait.');
                } else {
                    alert('Something went wrong.');
                }
            }
        });
    });

    // Open resolve modal
    $(document).on('click', '.sos-resolve-btn', function () {
        var alertId = $(this).data('id');
        $('#resolve-alert-id').val(alertId);
        $('#resolve-notes').val('');
        $('#sosResolveModal').modal('show');
    });

    // Confirm resolve
    $('#resolve-confirm-btn').on('click', function () {
        var btn = $(this);
        var alertId = $('#resolve-alert-id').val();
        btn.prop('disabled', true).text('Resolving...');

        $.ajax({
            url: baseUrl + '/roster/sos-alert/resolve',
            type: 'POST',
            data: {
                id: alertId,
                notes: $('#resolve-notes').val()
            },
            success: function (resp) {
                if (resp.success) {
                    $('#sosResolveModal').modal('hide');
                    loadSosAlerts();
                } else {
                    alert(resp.message || 'Failed to resolve alert.');
                }
            },
            error: function (xhr) {
                if (xhr.status === 403) {
                    alert('Only managers and admins can resolve alerts.');
                } else if (xhr.status === 429) {
                    alert('Too many requests. Please wait.');
                } else {
                    alert('Something went wrong.');
                }
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="fa fa-check"></i> Resolve');
            }
        });
    });

    function loadSosAlerts() {
        $.ajax({
            url: baseUrl + '/roster/sos-alert/list',
            type: 'POST',
            success: function (resp) {
                if (resp.success) {
                    renderAlerts(resp.data);
                } else {
                    $('#sos-alerts-container').html('<p class="text-muted text-center">Could not load alerts.</p>');
                }
            },
            error: function () {
                $('#sos-alerts-container').html('<p class="text-muted text-center">Could not load alerts.</p>');
            }
        });
    }

    function renderAlerts(alerts) {
        var container = $('#sos-alerts-container');
        var activeCount = 0;

        if (!alerts || alerts.length === 0) {
            container.html('<p class="text-muted text-center">No SOS alerts.</p>');
            $('#sos-active-count').text('');
            return;
        }

        var html = '';
        for (var i = 0; i < alerts.length; i++) {
            var a = alerts[i];
            var statusColor, statusLabel, borderStyle;

            if (a.status === 1) {
                statusColor = '#d9534f';
                statusLabel = 'Active';
                borderStyle = 'border-left: 4px solid #d9534f; background: #fdf2f2;';
                activeCount++;
            } else if (a.status === 2) {
                statusColor = '#f0ad4e';
                statusLabel = 'Acknowledged';
                borderStyle = 'border-left: 4px solid #f0ad4e; background: #fef9f0;';
            } else {
                statusColor = '#5cb85c';
                statusLabel = 'Resolved';
                borderStyle = 'border-left: 4px solid #5cb85c; background: #f2fdf2;';
            }

            var staffName = a.staff ? esc(a.staff.name) : 'Unknown';
            var createdAt = a.created_at ? new Date(a.created_at).toLocaleString() : '';
            var rawMessage = a.message ? esc(a.message) : '';
            var message = rawMessage.length > 200 ? rawMessage.substring(0, 200) + '...' : rawMessage;
            var ackName = a.acknowledged_by_user ? esc(a.acknowledged_by_user.name) : '';
            var resName = a.resolved_by_user ? esc(a.resolved_by_user.name) : '';

            html += '<div class="sos-alert-card" style="' + borderStyle + ' padding: 12px; margin-bottom: 10px; border-radius: 3px; word-break: break-word; overflow-wrap: break-word;">';
            html += '<div style="display: flex; justify-content: space-between; align-items: center;">';
            html += '<div>';
            html += '<strong>' + staffName + '</strong>';
            html += ' <span class="label" style="background: ' + statusColor + '; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 11px;">' + statusLabel + '</span>';
            html += '<br><small class="text-muted">' + createdAt + '</small>';
            if (message) {
                html += '<br><span style="font-size: 13px;">' + message + '</span>';
            }
            if (ackName) {
                html += '<br><small class="text-muted">Acknowledged by ' + ackName + '</small>';
            }
            if (resName) {
                html += '<br><small class="text-muted">Resolved by ' + resName + '</small>';
            }
            html += '</div>';
            html += '<div>';

            if (a.status === 1 && (userType === 'A' || userType === 'M')) {
                html += '<button class="btn btn-sm btn-warning sos-ack-btn" data-id="' + a.id + '" style="margin-right: 5px;"><i class="fa fa-check"></i> Acknowledge</button>';
                html += '<button class="btn btn-sm btn-success sos-resolve-btn" data-id="' + a.id + '"><i class="fa fa-check-circle"></i> Resolve</button>';
            } else if (a.status === 2 && (userType === 'A' || userType === 'M')) {
                html += '<button class="btn btn-sm btn-success sos-resolve-btn" data-id="' + a.id + '"><i class="fa fa-check-circle"></i> Resolve</button>';
            }

            html += '</div>';
            html += '</div>';
            html += '</div>';
        }

        container.html(html);
        $('#sos-active-count').text(activeCount > 0 ? activeCount : '');
    }
});
