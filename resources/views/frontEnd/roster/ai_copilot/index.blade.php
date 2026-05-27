@extends('frontEnd.layouts.master')
@section('title','AI Care Copilot')
@section('content')

@include('frontEnd.roster.common.roster_header')
<main class="page-content">

<style>
    .aic-container { padding: 20px 30px; height: calc(100vh - 140px); display: flex; gap: 20px; }
    .aic-sidebar {
        width: 280px; flex-shrink: 0; background: #fff; border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; flex-direction: column; overflow: hidden;
    }
    .aic-sidebar-header {
        padding: 16px; border-bottom: 1px solid #eee; display: flex;
        align-items: center; justify-content: space-between;
    }
    .aic-sidebar-header h3 { font-size: 15px; font-weight: 600; color: #2c3e50; margin: 0; }
    .aic-new-btn {
        background: #8e44ad; color: #fff; border: none; border-radius: 6px;
        padding: 6px 14px; font-size: 12px; font-weight: 600; cursor: pointer;
    }
    .aic-new-btn:hover { background: #7d3c98; color: #fff; }
    .aic-session-list { flex: 1; overflow-y: auto; padding: 8px; }
    .aic-session-item {
        padding: 10px 12px; border-radius: 8px; cursor: pointer; margin-bottom: 4px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .aic-session-item:hover { background: #f5f0f9; }
    .aic-session-item.active { background: #ede5f3; }
    .aic-session-item .title { font-size: 13px; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .aic-session-item .meta { font-size: 11px; color: #999; }
    .aic-chat-area {
        flex: 1; background: #fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        display: flex; flex-direction: column; overflow: hidden;
    }
    .aic-chat-header {
        padding: 16px; border-bottom: 1px solid #eee; display: flex;
        align-items: center; justify-content: space-between;
    }
    .aic-chat-header h3 { font-size: 16px; font-weight: 600; color: #2c3e50; margin: 0; }
    .aic-chat-messages { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 12px; }
    .aic-chat-input {
        display: flex; align-items: flex-end; padding: 16px; border-top: 1px solid #eee; gap: 10px;
    }
    .aic-chat-input textarea {
        flex: 1; border: 1px solid #ddd; border-radius: 8px; padding: 10px 14px;
        font-size: 14px; resize: none; max-height: 120px; font-family: inherit; outline: none;
    }
    .aic-chat-input textarea:focus { border-color: #8e44ad; }
    .aic-send-btn {
        width: 44px; height: 44px; background: #8e44ad; border: none; border-radius: 50%;
        color: #fff; font-size: 16px; cursor: pointer; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; padding: 0;
    }
    .aic-send-btn:hover { background: #7d3c98; }
    .aic-send-btn:disabled { background: #ccc; cursor: not-allowed; }
    .aic-empty-state {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        height: 100%; color: #999; text-align: center; padding: 40px;
    }
    .aic-empty-state i { font-size: 48px; margin-bottom: 16px; color: #8e44ad; }
    .aic-empty-state h4 { font-size: 18px; font-weight: 600; color: #555; margin-bottom: 8px; }
    .aic-empty-state p { font-size: 14px; }
    .aic-msg {
        max-width: 75%; padding: 12px 16px; border-radius: 12px;
        font-size: 14px; line-height: 1.6; word-wrap: break-word;
    }
    .aic-msg-user {
        align-self: flex-end; background: #2980b9; color: #fff; border-bottom-right-radius: 4px;
    }
    .aic-msg-assistant {
        align-self: flex-start; background: #f0f0f0; color: #333; border-bottom-left-radius: 4px;
    }
    .aic-msg-error {
        align-self: center; background: #fde8e8; color: #c0392b; text-align: center; font-size: 13px;
    }
    .aic-typing {
        align-self: flex-start; padding: 12px 16px; background: #f0f0f0;
        border-radius: 12px; display: flex; gap: 5px;
    }
    .aic-typing-dot {
        width: 8px; height: 8px; background: #999; border-radius: 50%;
        animation: aicTyping 1.4s infinite;
    }
    .aic-typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .aic-typing-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes aicTyping {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
        30% { transform: translateY(-5px); opacity: 1; }
    }
    .aic-token-info { font-size: 12px; color: #999; }
</style>

<div class="aic-container">
    <div class="aic-sidebar">
        <div class="aic-sidebar-header">
            <h3><i class="fa fa-commenting" style="color:#8e44ad;margin-right:6px;"></i> Chats</h3>
            <button class="aic-new-btn" onclick="aicPageNewSession()"><i class="fa fa-plus"></i> New</button>
        </div>
        <div class="aic-session-list" id="aic-page-sessions"></div>
    </div>
    <div class="aic-chat-area">
        <div class="aic-chat-header">
            <h3 id="aic-page-title">AI Care Copilot</h3>
            <span class="aic-token-info" id="aic-page-tokens"></span>
        </div>
        <div class="aic-chat-messages" id="aic-page-messages">
            <div class="aic-empty-state">
                <i class="fa fa-commenting"></i>
                <h4>AI Care Copilot</h4>
                <p>Start a new chat or select a previous conversation from the sidebar.</p>
            </div>
        </div>
        <div class="aic-chat-input">
            <textarea id="aic-page-textarea" placeholder="Type your message..." rows="2" maxlength="2000"></textarea>
            <button class="aic-send-btn" id="aic-page-send" onclick="aicPageSend()">
                <i class="fa fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    var pageSessionId = null;
    var pageLoading = false;
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    function esc(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function formatAI(text) {
        var e = esc(text);
        e = e.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        e = e.replace(/\n- /g, '\n• ').replace(/\n\* /g, '\n• ');
        e = e.replace(/\n/g, '<br>');
        return e;
    }

    function loadPageSessions() {
        $.get("{{ url('/roster/ai-copilot/sessions') }}", function(resp) {
            if (!resp.status) return;
            var list = document.getElementById('aic-page-sessions');
            if (resp.sessions.length === 0) {
                list.innerHTML = '<div style="padding:20px;text-align:center;color:#999;font-size:13px;">No chats yet</div>';
                return;
            }
            var html = '';
            for (var i = 0; i < resp.sessions.length; i++) {
                var s = resp.sessions[i];
                var cls = (s.id === pageSessionId) ? ' active' : '';
                html += '<div class="aic-session-item' + cls + '" onclick="aicPageLoadSession(' + parseInt(s.id) + ')">'
                      + '<span class="title">' + esc(s.session_title) + '</span>'
                      + '<span class="meta">' + (s.message_count || 0) + ' msgs</span>'
                      + '</div>';
            }
            list.innerHTML = html;
        });
    }

    function loadPageUsage() {
        $.get("{{ url('/roster/ai-copilot/usage') }}", function(resp) {
            if (!resp.status) return;
            var el = document.getElementById('aic-page-tokens');
            var used = resp.daily_usage || 0;
            var cap = resp.daily_cap || 100000;
            el.textContent = (used > 1000 ? Math.round(used/1000) + 'k' : used) + ' / ' + Math.round(cap/1000) + 'k tokens today';
        });
    }

    window.aicPageNewSession = function() {
        if (pageLoading) return;
        $.ajax({
            url: "{{ url('/roster/ai-copilot/new-session') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { context_type: 'general' },
            success: function(resp) {
                if (resp.status && resp.session) {
                    pageSessionId = resp.session.id;
                    document.getElementById('aic-page-messages').innerHTML = '';
                    document.getElementById('aic-page-title').textContent = 'New Chat';
                    loadPageSessions();
                }
            }
        });
    };

    window.aicPageLoadSession = function(id) {
        pageSessionId = id;
        var msgDiv = document.getElementById('aic-page-messages');
        msgDiv.innerHTML = '';

        $.get("{{ url('/roster/ai-copilot/messages') }}", { session_id: id }, function(resp) {
            if (!resp.status) return;
            for (var i = 0; i < resp.messages.length; i++) {
                appendPageMsg(resp.messages[i].role, resp.messages[i].content);
            }
            msgDiv.scrollTop = msgDiv.scrollHeight;
            loadPageSessions();
        });
    };

    window.aicPageSend = function() {
        var ta = document.getElementById('aic-page-textarea');
        var msg = (ta.value || '').trim();
        if (!msg || pageLoading) return;
        ta.value = '';
        ta.style.height = 'auto';

        if (!pageSessionId) {
            $.ajax({
                url: "{{ url('/roster/ai-copilot/new-session') }}",
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: { context_type: 'general' },
                success: function(resp) {
                    if (resp.status && resp.session) {
                        pageSessionId = resp.session.id;
                        document.getElementById('aic-page-messages').innerHTML = '';
                        doSend(msg);
                    }
                }
            });
            return;
        }
        doSend(msg);
    };

    function doSend(msg) {
        pageLoading = true;
        document.getElementById('aic-page-send').disabled = true;
        appendPageMsg('user', msg);

        var typing = document.createElement('div');
        typing.className = 'aic-typing'; typing.id = 'aic-page-typing';
        typing.innerHTML = '<div class="aic-typing-dot"></div><div class="aic-typing-dot"></div><div class="aic-typing-dot"></div>';
        document.getElementById('aic-page-messages').appendChild(typing);

        $.ajax({
            url: "{{ url('/roster/ai-copilot/send') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { message: msg, session_id: pageSessionId },
            timeout: 35000,
            success: function(resp) {
                var t = document.getElementById('aic-page-typing'); if (t) t.remove();
                if (resp.status) {
                    appendPageMsg('assistant', resp.message);
                    loadPageUsage();
                    loadPageSessions();
                } else {
                    appendPageError(resp.error || 'AI could not respond.');
                }
            },
            error: function(xhr) {
                var t = document.getElementById('aic-page-typing'); if (t) t.remove();
                if (xhr.status === 429) { appendPageError('Rate limit reached. Wait a moment.'); }
                else { appendPageError('AI is temporarily unavailable.'); }
            },
            complete: function() {
                pageLoading = false;
                document.getElementById('aic-page-send').disabled = false;
            }
        });
    }

    function appendPageMsg(role, content) {
        var container = document.getElementById('aic-page-messages');
        var empty = container.querySelector('.aic-empty-state');
        if (empty) empty.remove();

        var div = document.createElement('div');
        div.className = 'aic-msg aic-msg-' + (role === 'user' ? 'user' : 'assistant');
        div.innerHTML = role === 'user' ? esc(content) : formatAI(content);
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function appendPageError(msg) {
        var container = document.getElementById('aic-page-messages');
        var div = document.createElement('div');
        div.className = 'aic-msg aic-msg-error';
        div.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + esc(msg);
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    // Textarea shortcut
    var ta = document.getElementById('aic-page-textarea');
    if (ta) {
        ta.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); aicPageSend(); }
        });
        ta.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
    }

    loadPageSessions();
    loadPageUsage();
})();
</script>

</main>
@stop
