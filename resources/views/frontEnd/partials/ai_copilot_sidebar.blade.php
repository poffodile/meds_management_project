<!-- AI Copilot Sidebar -->
<div id="ai-copilot-panel" class="ai-copilot-panel" style="display:none;">
    <div class="ai-copilot-header">
        <div class="ai-copilot-title">
            <i class="fa fa-commenting"></i> AI Care Copilot
        </div>
        <div class="ai-copilot-actions">
            <span id="ai-copilot-tokens" class="ai-token-badge" title="Tokens used today">0 tokens</span>
            <button type="button" class="ai-btn-sm" onclick="aiCopilotNewSession()" title="New Chat">
                <i class="fa fa-plus"></i>
            </button>
            <button type="button" class="ai-btn-sm" id="ai-session-list-toggle" onclick="aiCopilotToggleSessions()" title="Chat History">
                <i class="fa fa-history"></i>
            </button>
            <button type="button" class="ai-btn-sm" onclick="aiCopilotClose()" title="Close">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>

    <div id="ai-copilot-sessions" class="ai-copilot-sessions" style="display:none;">
        <div class="ai-sessions-header">Chat History</div>
        <div id="ai-session-list" class="ai-session-list"></div>
    </div>

    <div id="ai-copilot-messages" class="ai-copilot-messages">
        <div class="ai-welcome-msg">
            <div class="ai-avatar"><i class="fa fa-commenting"></i></div>
            <p>Hi! I'm your <strong>Care Copilot</strong>. I can help with:</p>
            <ul>
                <li>Questions about residents</li>
                <li>Summarising care records</li>
                <li>Drafting handover notes</li>
                <li>General care advice</li>
            </ul>
            <p>How can I help you today?</p>
        </div>
    </div>

    <div class="ai-copilot-input">
        <textarea id="ai-copilot-textarea" placeholder="Type your message..." rows="2" maxlength="2000"></textarea>
        <button type="button" id="ai-copilot-send" onclick="aiCopilotSend()">
            <i class="fa fa-paper-plane"></i>
        </button>
    </div>
</div>

<style>
.ai-copilot-panel {
    position: fixed;
    top: 0;
    right: 0;
    width: 380px;
    height: 100vh;
    background: #fff;
    box-shadow: -3px 0 15px rgba(0,0,0,0.15);
    z-index: 10000;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
.ai-copilot-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: #8e44ad;
    color: #fff;
    min-height: 52px;
}
.ai-copilot-title {
    font-size: 15px;
    font-weight: 600;
}
.ai-copilot-title i { margin-right: 6px; }
.ai-copilot-actions { display: flex; align-items: center; gap: 6px; }
.ai-btn-sm {
    background: rgba(255,255,255,0.2);
    border: none;
    color: #fff;
    width: 30px;
    height: 30px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}
.ai-btn-sm:hover { background: rgba(255,255,255,0.35); }
.ai-token-badge {
    font-size: 11px;
    background: rgba(255,255,255,0.2);
    padding: 3px 8px;
    border-radius: 10px;
    white-space: nowrap;
}
.ai-copilot-sessions {
    border-bottom: 1px solid #e0e0e0;
    max-height: 250px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.ai-sessions-header {
    font-size: 12px;
    font-weight: 600;
    color: #666;
    padding: 8px 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.ai-session-list {
    overflow-y: auto;
    flex: 1;
}
.ai-session-item {
    padding: 8px 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
    border-bottom: 1px solid #f0f0f0;
}
.ai-session-item:hover { background: #f5f0f9; }
.ai-session-item.active { background: #ede5f3; font-weight: 600; }
.ai-session-item .ai-session-title { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ai-session-item .ai-session-delete {
    color: #ccc;
    cursor: pointer;
    padding: 2px 4px;
    font-size: 12px;
}
.ai-session-item .ai-session-delete:hover { color: #e74c3c; }
.ai-copilot-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.ai-welcome-msg {
    background: #f5f0f9;
    border-radius: 12px;
    padding: 16px;
    font-size: 13px;
    line-height: 1.5;
    color: #333;
}
.ai-welcome-msg .ai-avatar {
    width: 40px;
    height: 40px;
    background: #8e44ad;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
    margin-bottom: 10px;
}
.ai-welcome-msg ul {
    margin: 8px 0;
    padding-left: 20px;
}
.ai-welcome-msg li { margin: 3px 0; }
.ai-msg {
    max-width: 85%;
    padding: 10px 14px;
    border-radius: 12px;
    font-size: 13px;
    line-height: 1.5;
    word-wrap: break-word;
}
.ai-msg-user {
    align-self: flex-end;
    background: #2980b9;
    color: #fff;
    border-bottom-right-radius: 4px;
}
.ai-msg-assistant {
    align-self: flex-start;
    background: #f0f0f0;
    color: #333;
    border-bottom-left-radius: 4px;
}
.ai-msg-error {
    align-self: center;
    background: #fde8e8;
    color: #c0392b;
    text-align: center;
    font-size: 12px;
}
.ai-typing {
    align-self: flex-start;
    padding: 10px 14px;
    background: #f0f0f0;
    border-radius: 12px;
    display: flex;
    gap: 4px;
}
.ai-typing-dot {
    width: 7px;
    height: 7px;
    background: #999;
    border-radius: 50%;
    animation: aiTyping 1.4s infinite;
}
.ai-typing-dot:nth-child(2) { animation-delay: 0.2s; }
.ai-typing-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes aiTyping {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
    30% { transform: translateY(-4px); opacity: 1; }
}
.ai-copilot-input {
    display: flex;
    align-items: flex-end;
    padding: 12px;
    border-top: 1px solid #e0e0e0;
    background: #fafafa;
    gap: 8px;
}
.ai-copilot-input textarea {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 13px;
    resize: none;
    max-height: 100px;
    font-family: inherit;
    outline: none;
}
.ai-copilot-input textarea:focus { border-color: #8e44ad; }
#ai-copilot-send {
    width: 38px;
    height: 38px;
    background: #8e44ad;
    border: none;
    border-radius: 50%;
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    padding: 0;
}
#ai-copilot-send:hover { background: #7d3c98; }
#ai-copilot-send:disabled { background: #ccc; cursor: not-allowed; }
.ai-copilot-toggle {
    display: flex;
    background: #8e44ad;
    border-radius: 100%;
    bottom: 130px;
    color: #fff;
    font-size: 26px;
    padding: 10px;
    position: fixed;
    right: 40px;
    z-index: 9999;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    height: 50px;
    width: 50px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    transition: transform 0.2s;
}
.ai-copilot-toggle:hover { transform: scale(1.1); color: #fff; text-decoration: none; }
@media (max-width: 768px) {
    .ai-copilot-panel { width: 100vw; }
}
</style>
