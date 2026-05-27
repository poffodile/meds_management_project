$(function () {
    var selectedClientId = null;
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });

    function esc(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var day = d.getDate();
        var mon = months[d.getMonth()];
        var h = d.getHours();
        var m = d.getMinutes();
        return day + ' ' + mon + ', ' + (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
    }

    function priorityBadge(priority, isStaff) {
        if (priority === 'normal') return '';
        if (isStaff) {
            return '<span class="bubble-priority">' + esc(priority) + '</span>';
        }
        return '<span class="bubble-priority bp-' + esc(priority) + '">' + esc(priority) + '</span>';
    }

    function renderBubble(msg) {
        var isStaff = msg.sender_type === 'staff';
        var cls = isStaff ? 'bubble-staff' : 'bubble-family';
        var senderLabel = isStaff ? 'Care Team' : esc(msg.sender_name);

        var html = '<div class="chat-bubble ' + cls + '">';
        if (msg.subject && msg.subject !== 'Re: Message') {
            html += '<div class="bubble-subject">' + esc(msg.subject) + '</div>';
        }
        html += '<div class="bubble-sender">' + esc(msg.sender_name);
        html += ' <span style="font-weight:400; opacity:0.7">(' + senderLabel + ')</span>';
        html += priorityBadge(msg.priority, isStaff);
        html += '</div>';
        html += '<div class="bubble-body">' + esc(msg.message_content) + '</div>';
        html += '<div class="bubble-meta">' + formatDate(msg.created_at);
        if (msg.is_read) {
            html += ' &middot; Read';
        }
        html += '</div>';
        html += '</div>';
        return html;
    }

    // Client selection
    $(document).on('click', '.client-item', function () {
        var $item = $(this);
        var clientId = $item.data('client-id');
        var clientName = $item.data('client-name');

        selectedClientId = clientId;
        $('.client-item').removeClass('selected');
        $item.addClass('selected');

        $('#thread-header').show();
        $('#thread-client-name').text(clientName);
        $('#thread-placeholder').hide();
        $('#reply-box').show();
        $('#thread-messages').html('<div style="text-align:center; padding:40px; color:#aaa"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');

        $.ajax({
            url: '/roster/messaging-center/thread',
            type: 'POST',
            data: { client_id: clientId },
            success: function (res) {
                if (res.status && res.messages) {
                    var html = '';
                    for (var i = 0; i < res.messages.length; i++) {
                        html += renderBubble(res.messages[i]);
                    }
                    if (html === '') {
                        html = '<div style="text-align:center; padding:40px; color:#aaa">No messages yet</div>';
                    }
                    $('#thread-messages').html(html);
                    // Auto-scroll to bottom
                    var container = document.getElementById('thread-messages');
                    container.scrollTop = container.scrollHeight;

                    // Clear unread badge for this client
                    $item.find('.badge-unread').remove();
                    $item.find('.badge-urgent').remove();
                } else {
                    $('#thread-messages').html('<div style="text-align:center; padding:40px; color:#e74c3c">Failed to load messages</div>');
                }
            },
            error: function () {
                $('#thread-messages').html('<div style="text-align:center; padding:40px; color:#e74c3c">Error loading thread</div>');
            }
        });
    });

    // Client search filter
    $('#client-search').on('keyup', function () {
        var q = $(this).val().toLowerCase();
        $('.client-item').each(function () {
            var name = $(this).data('client-name').toString().toLowerCase();
            $(this).toggle(name.indexOf(q) !== -1);
        });
    });

    // Send reply
    function sendReply() {
        if (!selectedClientId) return;
        var content = $.trim($('#reply-input').val());
        if (!content) return;

        var $btn = $('#btn-send-reply');
        $btn.prop('disabled', true);

        $.ajax({
            url: '/roster/messaging-center/reply',
            type: 'POST',
            data: {
                client_id: selectedClientId,
                message_content: content
            },
            success: function (res) {
                if (res.status && res.message) {
                    var html = renderBubble(res.message);
                    $('#thread-messages').append(html);
                    $('#reply-input').val('');
                    var container = document.getElementById('thread-messages');
                    container.scrollTop = container.scrollHeight;
                } else {
                    alert('Failed to send reply: ' + (res.message || 'Unknown error'));
                }
                $btn.prop('disabled', false);
            },
            error: function (xhr) {
                var msg = 'Failed to send reply.';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).map(function (e) { return e[0]; }).join('\n');
                }
                alert(msg);
                $btn.prop('disabled', false);
            }
        });
    }

    $('#btn-send-reply').on('click', sendReply);

    // Enter to send, Shift+Enter for newline
    $('#reply-input').on('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendReply();
        }
    });

    // Auto-resize textarea
    $('#reply-input').on('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
});
