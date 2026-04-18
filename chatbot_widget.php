<!-- AI Chatbot Widget -->
<div id="chatbot-container" style="position: fixed; bottom: 24px; right: 24px; z-index: 9999; font-family: 'Segoe UI', sans-serif;">
    
    <!-- Chat Button -->
    <div id="chat-btn" onclick="toggleChat()" style="
        width: 56px; height: 56px; border-radius: 50%; background: #1A3A6B;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        transition: transform 0.2s;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2Z" fill="white"/>
        </svg>
        <span id="chat-badge" style="
            position: absolute; top: -4px; right: -4px;
            background: #dc3545; color: white; border-radius: 50%;
            width: 18px; height: 18px; font-size: 11px;
            display: flex; align-items: center; justify-content: center;
            display: none;">1</span>
    </div>

    <!-- Chat Window -->
    <div id="chat-window" style="
        display: none; position: absolute; bottom: 68px; right: 0;
        width: 340px; height: 480px; background: white;
        border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        flex-direction: column; overflow: hidden;">
        
        <!-- Header -->
        <div style="background: #1A3A6B; padding: 14px 16px; display: flex; align-items: center; gap: 10px;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.2);
                display: flex; align-items: center; justify-content: center;">
                🎓
            </div>
            <div>
                <div style="color: white; font-weight: 600; font-size: 14px;">Scholarship Assistant</div>
                <div style="color: #B5D4F4; font-size: 12px;">Powered by AI • Always here to help</div>
            </div>
            <div onclick="toggleChat()" style="margin-left: auto; color: white; cursor: pointer; font-size: 18px;">✕</div>
        </div>

        <!-- Messages -->
        <div id="chat-messages" style="flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 10px; height: 340px;">
            <div class="bot-msg" style="
                background: #f0f4f8; border-radius: 12px 12px 12px 4px;
                padding: 10px 14px; max-width: 85%; font-size: 13px; line-height: 1.5;">
                👋 Hi! I'm the Cainta Scholarship Assistant. How can I help you today?
                <div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;">
                    <button onclick="quickAsk('How do I apply for scholarship?')" style="background: #e8f0fe; border: none; border-radius: 20px; padding: 4px 10px; font-size: 11px; cursor: pointer; color: #1A3A6B;">How to apply?</button>
                    <button onclick="quickAsk('What are the requirements?')" style="background: #e8f0fe; border: none; border-radius: 20px; padding: 4px 10px; font-size: 11px; cursor: pointer; color: #1A3A6B;">Requirements?</button>
                    <button onclick="quickAsk('How do I track my application?')" style="background: #e8f0fe; border: none; border-radius: 20px; padding: 4px 10px; font-size: 11px; cursor: pointer; color: #1A3A6B;">Track status?</button>
                </div>
            </div>
        </div>

        <!-- Input -->
        <div style="padding: 12px 16px; border-top: 1px solid #f0f0f0; display: flex; gap: 8px;">
            <input type="text" id="chat-input" placeholder="Type your question..." 
                onkeypress="if(event.key==='Enter') sendMessage()"
                style="flex: 1; border: 1px solid #dde1e7; border-radius: 20px;
                padding: 8px 14px; font-size: 13px; outline: none;">
            <button onclick="sendMessage()" style="
                background: #1A3A6B; color: white; border: none;
                border-radius: 50%; width: 36px; height: 36px;
                cursor: pointer; display: flex; align-items: center; justify-content: center;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
function toggleChat() {
    const win = document.getElementById('chat-window');
    const btn = document.getElementById('chat-btn');
    if(win.style.display === 'none' || win.style.display === '') {
        win.style.display = 'flex';
        win.style.flexDirection = 'column';
        document.getElementById('chat-badge').style.display = 'none';
        document.getElementById('chat-input').focus();
    } else {
        win.style.display = 'none';
    }
}

function quickAsk(question) {
    document.getElementById('chat-input').value = question;
    sendMessage();
}

function sendMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    if(!message) return;

    // Add user message
    addMessage(message, 'user');
    input.value = '';

    // Show typing indicator
    const typingId = addTyping();

    // Send to API
    const formData = new FormData();
    formData.append('message', message);

    fetch('../chatbot.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        removeTyping(typingId);
        addMessage(data.reply, 'bot');
    })
    .catch(() => {
        removeTyping(typingId);
        addMessage('Sorry, something went wrong. Please try again.', 'bot');
    });
}

function addMessage(text, type) {
    const container = document.getElementById('chat-messages');
    const div = document.createElement('div');
    
    if(type === 'user') {
        div.style.cssText = 'background: #1A3A6B; color: white; border-radius: 12px 12px 4px 12px; padding: 10px 14px; max-width: 85%; font-size: 13px; line-height: 1.5; align-self: flex-end;';
    } else {
        div.style.cssText = 'background: #f0f4f8; border-radius: 12px 12px 12px 4px; padding: 10px 14px; max-width: 85%; font-size: 13px; line-height: 1.5;';
    }
    
    div.innerHTML = text.replace(/\n/g, '<br>');
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
    return div;
}

function addTyping(text, type) {
    const container = document.getElementById('chat-messages');
    const div = document.createElement('div');
    div.id = 'typing-' + Date.now();
    div.style.cssText = 'background: #f0f4f8; border-radius: 12px 12px 12px 4px; padding: 10px 14px; max-width: 85%; font-size: 13px;';
    div.innerHTML = '<span style="display:inline-flex;gap:4px;"><span style="animation:bounce 0.6s infinite">●</span><span style="animation:bounce 0.6s infinite 0.2s">●</span><span style="animation:bounce 0.6s infinite 0.4s">●</span></span>';
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;

    if(!document.getElementById('bounce-style')) {
        const style = document.createElement('style');
        style.id = 'bounce-style';
        style.innerHTML = '@keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-4px)}}';
        document.head.appendChild(style);
    }
    return div.id;
}

function removeTyping(id) {
    const el = document.getElementById(id);
    if(el) el.remove();
}
</script>