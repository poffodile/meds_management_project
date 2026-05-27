(function() {
    'use strict';

    var currentSessionId = null;
    var isLoading = false;
    var csrfToken = null;

    function esc(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function getCsrfToken() {
        if (csrfToken) return csrfToken;
        var meta = document.querySelector('meta[name="csrf-token"]');
        csrfToken = meta ? meta.getAttribute('content') : '';
        return csrfToken;
    }

    function getBaseUrl() {
        var base = document.querySelector('meta[name="base-url"]');
        if (base) return base.getAttribute('content');
        return '';
    }

    function formatAIResponse(text) {
        var escaped = esc(text);
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        escaped = escaped.replace(/\n- /g, '\n• ');
        escaped = escaped.replace(/\n\* /g, '\n• ');
        escaped = escaped.replace(/\n/g, '<br>');
        return escaped;
    }

    window.aiCopilotOpen = function() {
        var panel = document.getElementById('ai-copilot-panel');
        var toggle = document.getElementById('ai-copilot-toggle-btn');
        if (panel) {
            panel.style.display = 'flex';
            toggle.style.display = 'none';
            localStorage.setItem('ai_copilot_open', '1');

            if (!currentSessionId) {
                loadSessions();
                loadUsage();
            }
        }
    };

    window.aiCopilotClose = function() {
        var panel = document.getElementById('ai-copilot-panel');
        var toggle = document.getElementById('ai-copilot-toggle-btn');
        if (panel) {
            panel.style.display = 'none';
            toggle.style.display = 'flex';
            localStorage.setItem('ai_copilot_open', '0');
        }
    };

    window.aiCopilotToggleSessions = function() {
        var sessionsDiv = document.getElementById('ai-copilot-sessions');
        if (sessionsDiv.style.display === 'none') {
            sessionsDiv.style.display = 'flex';
            loadSessions();
        } else {
            sessionsDiv.style.display = 'none';
        }
    };

    window.aiCopilotNewSession = function() {
        if (isLoading) return;

        $.ajax({
            url: getBaseUrl() + '/roster/ai-copilot/new-session',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            data: { context_type: 'general' },
            success: function(resp) {
                if (resp.status && resp.session) {
                    currentSessionId = resp.session.id;
                    clearMessages();
                    showWelcome();
                    loadSessions();
                    loadUsage();
                }
            },
            error: function() {
                showError('Could not create a new chat session.');
            }
        });
    };

    window.aiCopilotSend = function() {
        var textarea = document.getElementById('ai-copilot-textarea');
        var message = (textarea.value || '').trim();
        if (!message || isLoading) return;

        textarea.value = '';
        textarea.style.height = 'auto';

        if (!currentSessionId) {
            createSessionThenSend(message);
            return;
        }

        sendMessage(message);
    };

    function createSessionThenSend(message) {
        $.ajax({
            url: getBaseUrl() + '/roster/ai-copilot/new-session',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            data: { context_type: 'general' },
            success: function(resp) {
                if (resp.status && resp.session) {
                    currentSessionId = resp.session.id;
                    clearMessages();
                    sendMessage(message);
                } else {
                    showError('Could not create chat session.');
                }
            },
            error: function() {
                showError('Could not create chat session.');
            }
        });
    }

    function sendMessage(message) {
        isLoading = true;
        var sendBtn = document.getElementById('ai-copilot-send');
        sendBtn.disabled = true;

        appendMessage('user', message);
        showTyping();

        $.ajax({
            url: getBaseUrl() + '/roster/ai-copilot/send',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            data: { message: message, session_id: currentSessionId },
            timeout: 35000,
            success: function(resp) {
                hideTyping();
                if (resp.status) {
                    appendMessage('assistant', resp.message);
                    if (resp.tokens_used) {
                        loadUsage();
                    }
                    loadSessions();
                } else {
                    showError(resp.error || 'AI could not respond. Please try again.');
                }
            },
            error: function(xhr) {
                hideTyping();
                if (xhr.status === 429) {
                    showError('Too many requests. Please wait a moment.');
                } else if (xhr.status === 422) {
                    var errors = xhr.responseJSON && xhr.responseJSON.errors;
                    var msg = errors ? Object.values(errors).flat().join(' ') : 'Invalid input.';
                    showError(msg);
                } else {
                    showError('AI is temporarily unavailable. Please try again later.');
                }
            },
            complete: function() {
                isLoading = false;
                sendBtn.disabled = false;
            }
        });
    }

    function loadSessions() {
        $.ajax({
            url: getBaseUrl() + '/roster/ai-copilot/sessions',
            method: 'GET',
            success: function(resp) {
                if (resp.status && resp.sessions) {
                    renderSessionList(resp.sessions);
                }
            }
        });
    }

    function renderSessionList(sessions) {
        var list = document.getElementById('ai-session-list');
        if (!list) return;

        if (sessions.length === 0) {
            list.innerHTML = '<div style="padding:12px 16px;color:#999;font-size:12px;">No previous chats</div>';
            return;
        }

        var html = '';
        for (var i = 0; i < sessions.length; i++) {
            var s = sessions[i];
            var activeClass = (s.id === currentSessionId) ? ' active' : '';
            html += '<div class="ai-session-item' + activeClass + '" data-id="' + esc(String(s.id)) + '">'
                  + '<span class="ai-session-title" onclick="aiCopilotLoadSession(' + parseInt(s.id) + ')">' + esc(s.session_title) + '</span>'
                  + '<span class="ai-session-delete" onclick="aiCopilotDeleteSession(event,' + parseInt(s.id) + ')" title="Delete">'
                  + '<i class="fa fa-trash-o"></i></span>'
                  + '</div>';
        }
        list.innerHTML = html;
    }

    window.aiCopilotLoadSession = function(sessionId) {
        currentSessionId = sessionId;
        clearMessages();

        $.ajax({
            url: getBaseUrl() + '/roster/ai-copilot/messages',
            method: 'GET',
            data: { session_id: sessionId },
            success: function(resp) {
                if (resp.status && resp.messages) {
                    for (var i = 0; i < resp.messages.length; i++) {
                        appendMessage(resp.messages[i].role, resp.messages[i].content);
                    }
                    scrollToBottom();
                    loadSessions();
                }
            }
        });

        var sessionsDiv = document.getElementById('ai-copilot-sessions');
        if (sessionsDiv) sessionsDiv.style.display = 'none';
    };

    window.aiCopilotDeleteSession = function(event, sessionId) {
        event.stopPropagation();
        if (!confirm('Delete this chat?')) return;

        $.ajax({
            url: getBaseUrl() + '/roster/ai-copilot/delete-session',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            data: { session_id: sessionId },
            success: function(resp) {
                if (resp.status) {
                    if (currentSessionId === sessionId) {
                        currentSessionId = null;
                        clearMessages();
                        showWelcome();
                    }
                    loadSessions();
                }
            }
        });
    };

    function loadUsage() {
        $.ajax({
            url: getBaseUrl() + '/roster/ai-copilot/usage',
            method: 'GET',
            success: function(resp) {
                if (resp.status) {
                    var badge = document.getElementById('ai-copilot-tokens');
                    if (badge) {
                        var used = resp.daily_usage || 0;
                        if (used > 1000) {
                            badge.textContent = Math.round(used / 1000) + 'k tokens';
                        } else {
                            badge.textContent = used + ' tokens';
                        }
                    }
                }
            }
        });
    }

    function appendMessage(role, content) {
        var container = document.getElementById('ai-copilot-messages');
        var welcome = container.querySelector('.ai-welcome-msg');
        if (welcome) welcome.remove();

        var div = document.createElement('div');
        div.className = 'ai-msg ai-msg-' + (role === 'user' ? 'user' : 'assistant');

        if (role === 'user') {
            div.innerHTML = esc(content);
        } else {
            div.innerHTML = formatAIResponse(content);
        }

        container.appendChild(div);
        scrollToBottom();
    }

    function showError(msg) {
        var container = document.getElementById('ai-copilot-messages');
        var div = document.createElement('div');
        div.className = 'ai-msg ai-msg-error';
        div.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + esc(msg);
        container.appendChild(div);
        scrollToBottom();
    }

    function showTyping() {
        var container = document.getElementById('ai-copilot-messages');
        var typing = document.createElement('div');
        typing.className = 'ai-typing';
        typing.id = 'ai-typing-indicator';
        typing.innerHTML = '<div class="ai-typing-dot"></div><div class="ai-typing-dot"></div><div class="ai-typing-dot"></div>';
        container.appendChild(typing);
        scrollToBottom();
    }

    function hideTyping() {
        var el = document.getElementById('ai-typing-indicator');
        if (el) el.remove();
    }

    function clearMessages() {
        var container = document.getElementById('ai-copilot-messages');
        if (container) container.innerHTML = '';
    }

    function showWelcome() {
        var container = document.getElementById('ai-copilot-messages');
        container.innerHTML = '<div class="ai-welcome-msg">'
            + '<div class="ai-avatar"><i class="fa fa-commenting"></i></div>'
            + '<p>Hi! I\'m your <strong>Care Copilot</strong>. I can help with:</p>'
            + '<ul><li>Questions about residents</li><li>Summarising care records</li>'
            + '<li>Drafting handover notes</li><li>General care advice</li></ul>'
            + '<p>How can I help you today?</p></div>';
    }

    function scrollToBottom() {
        var container = document.getElementById('ai-copilot-messages');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    // Textarea: Enter to send, Shift+Enter for newline
    document.addEventListener('DOMContentLoaded', function() {
        var textarea = document.getElementById('ai-copilot-textarea');
        if (textarea) {
            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    aiCopilotSend();
                }
            });

            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 100) + 'px';
            });
        }

        // Restore sidebar state from localStorage
        if (localStorage.getItem('ai_copilot_open') === '1') {
            aiCopilotOpen();
        }
    });

})();
