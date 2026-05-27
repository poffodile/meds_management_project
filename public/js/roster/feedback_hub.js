$(document).ready(function () {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });

    var currentStatus = '';
    var currentType = '';

    function esc(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function renderStars(rating) {
        var html = '';
        for (var i = 1; i <= 5; i++) {
            html += '<i class="fa ' + (i <= rating ? 'fa-star' : 'fa-star-o') + '"></i>';
        }
        return html;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var day = d.getDate();
        var mon = months[d.getMonth()];
        var yr = d.getFullYear();
        var hr = String(d.getHours()).padStart(2, '0');
        var mn = String(d.getMinutes()).padStart(2, '0');
        return day + ' ' + mon + ' ' + yr + ', ' + hr + ':' + mn;
    }

    function renderFeedbackItem(fb) {
        var typeClass = 'fb-' + esc(fb.feedback_type);
        var submitter = fb.is_anonymous ? 'Anonymous' : esc(fb.submitted_by);
        var clientName = esc(fb.client_name || 'Unknown');

        var html = '<div class="fb-item ' + typeClass + '" data-id="' + fb.id + '">';

        // Row 1: subject + badges
        html += '<div class="fb-row1">';
        html += '<span class="fb-subject">' + esc(fb.subject) + '</span>';
        html += '<span class="fb-badge fb-badge-' + esc(fb.feedback_type) + '">' + esc(ucfirst(fb.feedback_type)) + '</span>';
        html += '<span class="fb-badge fb-badge-' + esc(fb.status) + '">' + esc(ucfirst(fb.status)) + '</span>';
        html += '<span class="fb-badge fb-badge-cat">' + esc(fb.category ? fb.category.replace(/_/g, ' ') : '') + '</span>';
        if (fb.priority === 'high') {
            html += '<span class="fb-badge fb-badge-high">High Priority</span>';
        }
        html += '</div>';

        // Row 2: submitter, client, date
        html += '<div class="fb-row2">';
        html += '<span><i class="fa fa-user"></i> From: ' + submitter + '</span>';
        html += '<span><i class="fa fa-user-circle"></i> Client: ' + clientName + '</span>';
        html += '<span><i class="fa fa-clock-o"></i> ' + formatDate(fb.created_at) + '</span>';
        html += '</div>';

        // Stars
        html += '<div class="fb-stars">' + renderStars(fb.rating) + '</div>';

        // Comments
        html += '<div class="fb-comments">' + esc(fb.comments) + '</div>';

        // Callback request
        if (fb.wants_callback) {
            html += '<div class="fb-callback-box"><i class="fa fa-phone"></i> <strong>Contact requested</strong>';
            if (fb.contact_email) html += ' &middot; ' + esc(fb.contact_email);
            if (fb.contact_phone) html += ' &middot; ' + esc(fb.contact_phone);
            html += '</div>';
        }

        // Response
        if (fb.response) {
            html += '<div class="fb-response-box">';
            html += '<div class="resp-label"><i class="fa fa-reply"></i> Response</div>';
            html += '<div class="resp-text">' + esc(fb.response) + '</div>';
            html += '<div class="resp-meta">' + esc(fb.responded_by_name || 'Staff') + ' &middot; ' + formatDate(fb.response_date) + '</div>';
            html += '</div>';
        }

        // Actions
        html += '<div class="fb-actions">';
        if (fb.status === 'new') {
            html += '<button class="btn btn-sm btn-info btn-acknowledge" data-id="' + fb.id + '"><i class="fa fa-check"></i> Acknowledge</button>';
            html += '<button class="btn btn-sm btn-primary btn-respond" data-id="' + fb.id + '" data-subject="' + esc(fb.subject) + '" data-comments="' + esc(fb.comments) + '"><i class="fa fa-reply"></i> Respond</button>';
        } else if (fb.status === 'acknowledged') {
            html += '<button class="btn btn-sm btn-primary btn-respond" data-id="' + fb.id + '" data-subject="' + esc(fb.subject) + '" data-comments="' + esc(fb.comments) + '"><i class="fa fa-reply"></i> Respond</button>';
        } else if (fb.status === 'resolved') {
            html += '<button class="btn btn-sm btn-default btn-close-fb" data-id="' + fb.id + '"><i class="fa fa-times-circle"></i> Close</button>';
        }
        html += '</div>';

        html += '</div>';
        return html;
    }

    function ucfirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function loadFeedback() {
        var $list = $('#feedback-list');
        $list.html('<div class="loading-spinner"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Loading feedback...</div>');

        var params = {};
        if (currentStatus) params.status = currentStatus;
        if (currentType) params.type = currentType;

        $.ajax({
            url: window.location.origin + '/roster/feedback-hub/list',
            method: 'GET',
            data: params,
            success: function (res) {
                if (res.status && res.feedback && res.feedback.length > 0) {
                    var html = '';
                    for (var i = 0; i < res.feedback.length; i++) {
                        html += renderFeedbackItem(res.feedback[i]);
                    }
                    $list.html(html);
                } else {
                    $list.html('<div class="empty-fb"><i class="fa fa-comments-o"></i><h4>No feedback found</h4><p style="color:#aaa">No feedback matches the current filters</p></div>');
                }
            },
            error: function () {
                $list.html('<div class="empty-fb"><i class="fa fa-exclamation-triangle"></i><h4>Error loading feedback</h4></div>');
            }
        });
    }

    // Status filter tabs
    $('.filter-bar .btn').on('click', function () {
        $('.filter-bar .btn').removeClass('active');
        $(this).addClass('active');
        currentStatus = $(this).data('status') || '';
        loadFeedback();
    });

    // Type filter dropdown
    $('#filter-type').on('change', function () {
        currentType = $(this).val();
        loadFeedback();
    });

    // Acknowledge
    $(document).on('click', '.btn-acknowledge', function () {
        var feedbackId = $(this).data('id');
        var $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: window.location.origin + '/roster/feedback-hub/acknowledge',
            method: 'POST',
            data: { feedback_id: feedbackId },
            success: function (res) {
                if (res.status) {
                    loadFeedback();
                } else {
                    alert('Failed to acknowledge feedback');
                    $btn.prop('disabled', false);
                }
            },
            error: function () {
                alert('Error acknowledging feedback');
                $btn.prop('disabled', false);
            }
        });
    });

    // Open respond modal
    $(document).on('click', '.btn-respond', function () {
        var feedbackId = $(this).data('id');
        var subject = $(this).data('subject');
        var comments = $(this).data('comments');
        $('#respond-feedback-id').val(feedbackId);
        $('#respond-subject').text(subject);
        $('#respond-comments').text(comments);
        $('#respond-text').val('');
        $('#respond-modal').modal('show');
    });

    // Send response
    $('#btn-send-response').on('click', function () {
        var feedbackId = $('#respond-feedback-id').val();
        var responseText = $('#respond-text').val().trim();
        if (!responseText) {
            alert('Please enter a response');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');

        $.ajax({
            url: window.location.origin + '/roster/feedback-hub/respond',
            method: 'POST',
            data: { feedback_id: feedbackId, response: responseText },
            success: function (res) {
                if (res.status) {
                    $('#respond-modal').modal('hide');
                    loadFeedback();
                } else {
                    alert('Failed to send response');
                }
            },
            error: function () {
                alert('Error sending response');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa fa-reply"></i> Send Response');
            }
        });
    });

    // Close feedback
    $(document).on('click', '.btn-close-fb', function () {
        var feedbackId = $(this).data('id');
        var $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: window.location.origin + '/roster/feedback-hub/close',
            method: 'POST',
            data: { feedback_id: feedbackId },
            success: function (res) {
                if (res.status) {
                    loadFeedback();
                } else {
                    alert('Failed to close feedback');
                    $btn.prop('disabled', false);
                }
            },
            error: function () {
                alert('Error closing feedback');
                $btn.prop('disabled', false);
            }
        });
    });

    // Initial load
    loadFeedback();
});
