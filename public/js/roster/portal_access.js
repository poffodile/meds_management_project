var portalAccessLoaded = false;

function loadPortalAccess() {
    if (portalAccessLoaded) return;
    portalAccessLoaded = true;

    $.ajax({
        url: baseUrl + '/roster/client/portal-access-list',
        type: 'POST',
        data: { client_id: portalClientId },
        success: function(res) {
            if (res.status && res.data) {
                renderPortalAccessTable(res.data);
            } else {
                $('#portalAccessTableBody').html('<tr><td colspan="7" class="text-center text-muted">No portal access records found.</td></tr>');
            }
        },
        error: function(xhr) {
            $('#portalAccessTableBody').html('<tr><td colspan="7" class="text-center text-danger">Failed to load portal access list.</td></tr>');
        }
    });
}

function renderPortalAccessTable(data) {
    var tbody = $('#portalAccessTableBody');
    tbody.empty();

    if (!data.length) {
        tbody.html('<tr><td colspan="7" class="text-center text-muted">No portal access records found. Click "Grant Access" to add one.</td></tr>');
        return;
    }

    data.forEach(function(item) {
        var statusBadge = item.is_active
            ? '<span class="label label-success">Active</span>'
            : '<span class="label label-default">Revoked</span>';
        var lastLogin = item.last_login || 'Never';
        var actions = '';
        if (item.is_active) {
            actions += '<button class="btn btn-xs btn-warning btnRevokeAccess" data-id="' + item.id + '" title="Revoke"><i class="fa fa-ban"></i></button> ';
        }
        actions += '<button class="btn btn-xs btn-danger btnDeleteAccess" data-id="' + item.id + '" title="Delete"><i class="fa fa-trash"></i></button>';

        tbody.append(
            '<tr>' +
            '<td>' + esc(item.full_name) + '</td>' +
            '<td>' + esc(item.user_email) + '</td>' +
            '<td>' + esc(item.relationship) + '</td>' +
            '<td>' + esc(item.access_level) + '</td>' +
            '<td>' + statusBadge + '</td>' +
            '<td>' + esc(lastLogin) + '</td>' +
            '<td>' + actions + '</td>' +
            '</tr>'
        );
    });
}

$(document).ready(function() {
    $('#btnAddPortalAccess').on('click', function() {
        $('#portalAccessFormContainer').slideDown();
        $('#portalAccessFormError').text('');
    });

    $('#btnCancelPortalAccess').on('click', function() {
        $('#portalAccessFormContainer').slideUp();
        clearPortalForm();
    });

    $('#btnSavePortalAccess').on('click', function() {
        var fullName = $('#pa_full_name').val().trim();
        var email = $('#pa_user_email').val().trim();
        var relationship = $('#pa_relationship').val();

        if (!fullName || !email || !relationship) {
            $('#portalAccessFormError').text('Please fill in all required fields (Name, Email, Relationship).');
            return;
        }

        var data = {
            client_id: portalClientId,
            full_name: fullName,
            user_email: email,
            relationship: relationship,
            access_level: $('#pa_access_level').val(),
            phone: $('#pa_phone').val().trim(),
            notes: $('#pa_notes').val().trim(),
            is_primary_contact: $('#pa_is_primary_contact').is(':checked') ? 1 : 0,
            can_view_schedule: $('#pa_can_view_schedule').is(':checked') ? 1 : 0,
            can_view_care_notes: $('#pa_can_view_care_notes').is(':checked') ? 1 : 0,
            can_send_messages: $('#pa_can_send_messages').is(':checked') ? 1 : 0,
            can_request_bookings: $('#pa_can_request_bookings').is(':checked') ? 1 : 0
        };

        $('#btnSavePortalAccess').prop('disabled', true);

        $.ajax({
            url: baseUrl + '/roster/client/portal-access-save',
            type: 'POST',
            data: data,
            success: function(res) {
                if (res.status) {
                    $('#portalAccessFormContainer').slideUp();
                    clearPortalForm();
                    portalAccessLoaded = false;
                    loadPortalAccess();
                    alert('Portal access granted successfully.');
                } else {
                    $('#portalAccessFormError').text(res.message || 'Failed to save portal access.');
                }
            },
            error: function(xhr) {
                var msg = 'Failed to save portal access.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
                $('#portalAccessFormError').text(msg);
            },
            complete: function() {
                $('#btnSavePortalAccess').prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.btnRevokeAccess', function() {
        if (!confirm('Are you sure you want to revoke this portal access?')) return;
        var id = $(this).data('id');
        $.ajax({
            url: baseUrl + '/roster/client/portal-access-revoke',
            type: 'POST',
            data: { id: id },
            success: function(res) {
                if (res.status) {
                    portalAccessLoaded = false;
                    loadPortalAccess();
                } else {
                    alert(res.message || 'Failed to revoke access.');
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to revoke access.');
            }
        });
    });

    $(document).on('click', '.btnDeleteAccess', function() {
        if (!confirm('Are you sure you want to permanently delete this portal access record?')) return;
        var id = $(this).data('id');
        $.ajax({
            url: baseUrl + '/roster/client/portal-access-delete',
            type: 'POST',
            data: { id: id },
            success: function(res) {
                if (res.status) {
                    portalAccessLoaded = false;
                    loadPortalAccess();
                } else {
                    alert(res.message || 'Failed to delete access.');
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to delete access.');
            }
        });
    });
});

function clearPortalForm() {
    $('#pa_full_name').val('');
    $('#pa_user_email').val('');
    $('#pa_relationship').val('');
    $('#pa_access_level').val('view_and_message');
    $('#pa_phone').val('');
    $('#pa_notes').val('');
    $('#pa_is_primary_contact').prop('checked', false);
    $('#pa_can_view_schedule').prop('checked', true);
    $('#pa_can_view_care_notes').prop('checked', true);
    $('#pa_can_send_messages').prop('checked', true);
    $('#pa_can_request_bookings').prop('checked', false);
    $('#portalAccessFormError').text('');
}
