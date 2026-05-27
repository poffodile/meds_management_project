function esc(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

var eventTypeMap = {
    1:  {label: 'Health Record',   icon: 'fa-heartbeat',      color: '#e74c3c'},
    2:  {label: 'Daily Record',    icon: 'fa-file-text-o',    color: '#3498db'},
    3:  {label: 'Earning Scheme',  icon: 'fa-star',           color: '#f39c12'},
    4:  {label: 'Placement Plan',  icon: 'fa-clipboard',      color: '#9b59b6'},
    5:  {label: 'MFC/AFC',         icon: 'fa-users',          color: '#1abc9c'},
    6:  {label: 'Living Skills',   icon: 'fa-home',           color: '#2ecc71'},
    7:  {label: 'Education',       icon: 'fa-graduation-cap', color: '#e67e22'},
    8:  {label: 'BMP',             icon: 'fa-file-text',      color: '#16a085'},
    9:  {label: 'RMP',             icon: 'fa-shield',         color: '#c0392b'},
    10: {label: 'Incident Report', icon: 'fa-exclamation-triangle', color: '#e74c3c'},
    11: {label: 'Risk',            icon: 'fa-warning',        color: '#d35400'},
    12: {label: 'Top Profile',     icon: 'fa-user-circle',    color: '#8e44ad'},
    13: {label: 'AFC',             icon: 'fa-users',          color: '#27ae60'},
    14: {label: 'In Danger',       icon: 'fa-exclamation-circle', color: '#c0392b'},
    15: {label: 'Callback',        icon: 'fa-phone',          color: '#2980b9'},
    16: {label: 'Assistance',      icon: 'fa-hand-paper-o',   color: '#e67e22'},
    17: {label: 'Location',        icon: 'fa-map-marker',     color: '#1abc9c'},
    18: {label: 'Money Request',   icon: 'fa-money',          color: '#27ae60'},
    19: {label: 'Event Record',    icon: 'fa-calendar',       color: '#3498db'},
    20: {label: 'Event Change',    icon: 'fa-calendar-times-o', color: '#e74c3c'},
    21: {label: 'Mood',            icon: 'fa-smile-o',        color: '#f1c40f'},
    22: {label: 'Behavior',        icon: 'fa-user',           color: '#9b59b6'},
    23: {label: 'Log Book',        icon: 'fa-book',           color: '#34495e'},
    24: {label: 'SOS Alert',       icon: 'fa-bell',           color: '#e74c3c'}
};

var currentPage = 1;
var isLoading = false;

$(document).ready(function() {
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': csrfToken} });

    loadNotifications(1);

    $('#filter-btn').on('click', function() {
        currentPage = 1;
        loadNotifications(1);
    });

    $('#clear-filter-btn').on('click', function() {
        $('#filter-type').val('');
        $('#filter-start-date').val('');
        $('#filter-end-date').val('');
        currentPage = 1;
        loadNotifications(1);
    });

    $('#load-more-btn').on('click', function() {
        currentPage++;
        loadNotifications(currentPage, true);
    });

    $('#mark-all-read-btn').on('click', function() {
        if (!confirm('Mark all notifications as read?')) return;
        var btn = $(this);
        btn.prop('disabled', true);
        $.ajax({
            url: baseUrl + '/roster/notifications/mark-all-read',
            type: 'POST',
            success: function(res) {
                if (res.success) {
                    $('.notif-card').removeClass('notif-unread').addClass('notif-read');
                    $('.notif-card .mark-read-btn').remove();
                    updateBadgeCount();
                    alert(res.message);
                }
            },
            error: function(xhr) {
                var msg = 'Something went wrong.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            },
            complete: function() { btn.prop('disabled', false); }
        });
    });
});

function loadNotifications(page, append) {
    if (isLoading) return;
    isLoading = true;

    var data = { page: page };
    var typeId = $('#filter-type').val();
    var startDate = $('#filter-start-date').val();
    var endDate = $('#filter-end-date').val();

    if (typeId) data.type_id = typeId;
    if (startDate) data.start_date = startDate;
    if (endDate) data.end_date = endDate;

    if (!append) {
        $('#notification-list').html('<div class="text-center" style="padding:40px;"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading notifications...</p></div>');
    }

    $.ajax({
        url: baseUrl + '/roster/notifications/list',
        type: 'POST',
        data: data,
        success: function(res) {
            if (!res.success) {
                $('#notification-list').html('<div class="text-center" style="padding:40px;"><p>Failed to load notifications.</p></div>');
                return;
            }

            var d = res.data;
            var html = '';

            if (d.notifications.length === 0 && !append) {
                html = '<div class="text-center" style="padding:40px;color:#888;"><i class="fa fa-bell-slash-o fa-3x" style="margin-bottom:10px;"></i><p>No notifications found.</p></div>';
                $('#notification-pagination').hide();
            } else {
                for (var i = 0; i < d.notifications.length; i++) {
                    html += renderNotificationCard(d.notifications[i]);
                }

                if (d.page < d.last_page) {
                    $('#notification-pagination').show();
                } else {
                    $('#notification-pagination').hide();
                }
            }

            if (append) {
                $('#notification-list').append(html);
            } else {
                $('#notification-list').html(html);
            }
        },
        error: function(xhr) {
            if (!append) {
                $('#notification-list').html('<div class="text-center" style="padding:40px;"><p>Failed to load notifications. Please try again.</p></div>');
            }
        },
        complete: function() { isLoading = false; }
    });
}

function renderNotificationCard(n) {
    var typeInfo = eventTypeMap[n.notification_event_type_id] || {label: 'Notification', icon: 'fa-bell', color: '#95a5a6'};
    var isUnread = (!n.sticky_master_ack || n.sticky_master_ack == 0);
    var cardClass = isUnread ? 'notif-unread' : 'notif-read';
    var timeAgo = formatTimeAgo(n.created_at);
    var markBtn = isUnread ? '<button class="btn btn-xs btn-default mark-read-btn" onclick="markAsRead(' + parseInt(n.id) + ', this)"><i class="fa fa-check"></i> Mark Read</button>' : '';

    var html = '<div class="notif-card ' + cardClass + '" data-id="' + parseInt(n.id) + '">';
    html += '<div class="notif-icon" style="background:' + esc(typeInfo.color) + ';"><i class="fa ' + esc(typeInfo.icon) + '"></i></div>';
    html += '<div class="notif-body">';
    html += '<div class="notif-header">';
    html += '<span class="notif-type">' + esc(typeInfo.label) + '</span>';
    html += '<span class="notif-action">' + esc(n.event_action || '') + '</span>';
    html += '<span class="notif-time">' + esc(timeAgo) + '</span>';
    html += '</div>';
    html += '<div class="notif-message">' + esc(n.message || 'No message') + '</div>';
    html += '</div>';
    html += '<div class="notif-actions">' + markBtn + '</div>';
    html += '</div>';

    return html;
}

function markAsRead(id, btn) {
    $.ajax({
        url: baseUrl + '/roster/notifications/mark-read',
        type: 'POST',
        data: { id: id },
        success: function(res) {
            if (res.success) {
                var card = $('.notif-card[data-id="' + id + '"]');
                card.removeClass('notif-unread').addClass('notif-read');
                $(btn).remove();
                updateBadgeCount();
            }
        },
        error: function(xhr) {
            var msg = 'Something went wrong.';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            alert(msg);
        }
    });
}

function updateBadgeCount() {
    $.ajax({
        url: baseUrl + '/roster/notifications/unread-count',
        type: 'POST',
        success: function(res) {
            if (res.success) {
                var badge = $('#notification-badge');
                if (res.count > 0) {
                    badge.text(res.count > 99 ? '99+' : res.count).show();
                } else {
                    badge.hide();
                }
            }
        }
    });
}

function formatTimeAgo(dateStr) {
    if (!dateStr) return '';
    var date = new Date(dateStr);
    var now = new Date();
    var diffMs = now - date;
    var diffMins = Math.floor(diffMs / 60000);
    var diffHrs = Math.floor(diffMs / 3600000);
    var diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return diffMins + 'm ago';
    if (diffHrs < 24) return diffHrs + 'h ago';
    if (diffDays < 7) return diffDays + 'd ago';
    return date.toLocaleDateString('en-GB', {day: 'numeric', month: 'short', year: 'numeric'});
}
