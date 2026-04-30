<!-- AI Chatbot Widget -->
<div id="chatbot-container" style="position: fixed; bottom: 24px; right: 24px; z-index: 9999; font-family: 'Segoe UI', sans-serif;">

    <!-- Chat Button -->
    <div id="chat-btn" onclick="toggleChat()" style="
        width: 56px; height: 56px; border-radius: 50%; background: #1A3A6B;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        transition: transform 0.2s; position: relative;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2Z" fill="white"/>
        </svg>
        <span id="chat-badge" style="
            position: absolute; top: -4px; right: -4px;
            background: #dc3545; color: white; border-radius: 50%;
            width: 18px; height: 18px; font-size: 11px;
            display: none; align-items: center; justify-content: center;">1</span>
    </div>

    <!-- Chat Window -->
    <div id="chat-window" style="
        display: none; position: absolute; bottom: 68px; right: 0;
        width: 340px; height: 520px; background: white;
        border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        flex-direction: column; overflow: hidden;">

        <!-- Header -->
        <div style="background: #1A3A6B; padding: 14px 16px; display: flex; align-items: center; gap: 10px;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.2);
                display: flex; align-items: center; justify-content: center;">
                🎓
            </div>
            <div>
                <div id="header-title" style="color: white; font-weight: 600; font-size: 14px;">Scholarship Assistant</div>
                <div id="header-sub" style="color: #B5D4F4; font-size: 12px;">Powered by AI • Always here to help</div>
            </div>
            <div onclick="toggleChat()" style="margin-left: auto; color: white; cursor: pointer; font-size: 18px;">✕</div>
        </div>

        <!-- Language Toggle -->
        <div style="background: #f8f9fa; padding: 8px 16px; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; justify-content: space-between;">
            <span id="lang-label" style="font-size: 12px; color: #666;">Language:</span>
            <div style="display: flex; gap: 6px;">
                <button id="btn-en" onclick="setLang('en')" style="
                    background: #1A3A6B; color: white; border: none;
                    border-radius: 20px; padding: 4px 12px; font-size: 11px;
                    cursor: pointer; font-weight: 600; transition: all 0.2s;">
                    🇺🇸 English
                </button>
                <button id="btn-tl" onclick="setLang('tl')" style="
                    background: #e9ecef; color: #555; border: none;
                    border-radius: 20px; padding: 4px 12px; font-size: 11px;
                    cursor: pointer; font-weight: 600; transition: all 0.2s;">
                    🇵🇭 Filipino
                </button>
            </div>
        </div>

        <!-- Messages -->
        <div id="chat-messages" style="flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 10px; height: 310px;">
            <!-- Welcome message injected by JS on load -->
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

<style>
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}
</style>

<script>
let currentLang = 'en';

const translations = {
    en: {
        headerTitle: 'Scholarship Assistant',
        headerSub: 'Powered by AI • Always here to help',
        langLabel: 'Language:',
        placeholder: 'Type your question...',
        welcome: '👋 Hi! I\'m the Cainta Scholarship Assistant. How can I help you today?',
        quick1: 'How to apply?',
        quick2: 'Requirements?',
        quick3: 'Track status?',
        quick1q: 'How do I apply for scholarship?',
        quick2q: 'What are the requirements?',
        quick3q: 'How do I track my application?',
        errorMsg: 'Sorry, something went wrong. Please try again.',
    },
    tl: {
        headerTitle: 'Katulong sa Scholarship',
        headerSub: 'Pinapagana ng AI • Lagi kaming nandito',
        langLabel: 'Wika:',
        placeholder: 'I-type ang iyong tanong...',
        welcome: '👋 Kumusta! Ako ang Katulong ng Cainta Scholarship. Paano kita matutulungan ngayon?',
        quick1: 'Paano mag-apply?',
        quick2: 'Mga kinakailangan?',
        quick3: 'I-track ang status?',
        quick1q: 'Paano ako mag-apply para sa scholarship?',
        quick2q: 'Ano ang mga kinakailangang dokumento?',
        quick3q: 'Paano ko ma-track ang aking aplikasyon?',
        errorMsg: 'Paumanhin, may nangyaring mali. Pakisubukang muli.',
    }
};

function setLang(lang) {
    currentLang = lang;
    const t = translations[lang];

    // Update header
    document.getElementById('header-title').textContent = t.headerTitle;
    document.getElementById('header-sub').textContent = t.headerSub;
    document.getElementById('lang-label').textContent = t.langLabel;
    document.getElementById('chat-input').placeholder = t.placeholder;

    // Update button styles
    document.getElementById('btn-en').style.background = lang === 'en' ? '#1A3A6B' : '#e9ecef';
    document.getElementById('btn-en').style.color = lang === 'en' ? 'white' : '#555';
    document.getElementById('btn-tl').style.background = lang === 'tl' ? '#1A3A6B' : '#e9ecef';
    document.getElementById('btn-tl').style.color = lang === 'tl' ? 'white' : '#555';

    // Clear and re-render welcome message
    const container = document.getElementById('chat-messages');
    container.innerHTML = '';
    addWelcomeMessage();
}

function addWelcomeMessage() {
    const t = translations[currentLang];
    const container = document.getElementById('chat-messages');
    const div = document.createElement('div');
    div.style.cssText = 'background: #f0f4f8; border-radius: 12px 12px 12px 4px; padding: 10px 14px; max-width: 85%; font-size: 13px; line-height: 1.5;';
    div.innerHTML = `${t.welcome}
        <div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;">
            <button onclick="quickAsk('${t.quick1q}')" style="background: #e8f0fe; border: none; border-radius: 20px; padding: 4px 10px; font-size: 11px; cursor: pointer; color: #1A3A6B;">${t.quick1}</button>
            <button onclick="quickAsk('${t.quick2q}')" style="background: #e8f0fe; border: none; border-radius: 20px; padding: 4px 10px; font-size: 11px; cursor: pointer; color: #1A3A6B;">${t.quick2}</button>
            <button onclick="quickAsk('${t.quick3q}')" style="background: #e8f0fe; border: none; border-radius: 20px; padding: 4px 10px; font-size: 11px; cursor: pointer; color: #1A3A6B;">${t.quick3}</button>
        </div>`;
    container.appendChild(div);
}

function toggleChat() {
    const win = document.getElementById('chat-window');
    if (win.style.display === 'none' || win.style.display === '') {
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
    if (!message) return;

    addMessage(message, 'user');
    input.value = '';

    const typingId = addTyping();

    const formData = new FormData();
    formData.append('message', message);
    formData.append('lang', currentLang);

    const path = window.location.pathname;
    const isSubfolder = path.includes('/student/') || path.includes('/admin/') || path.includes('/officer/') || path.includes('/cashier/');
    const chatbotUrl = isSubfolder ? '../chatbot.php' : 'chatbot.php';

    fetch(chatbotUrl, {
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
        addMessage(translations[currentLang].errorMsg, 'bot');
    });
}

function addMessage(text, type) {
    const container = document.getElementById('chat-messages');
    const div = document.createElement('div');
    if (type === 'user') {
        div.style.cssText = 'background: #1A3A6B; color: white; border-radius: 12px 12px 4px 12px; padding: 10px 14px; max-width: 85%; font-size: 13px; line-height: 1.5; align-self: flex-end;';
    } else {
        div.style.cssText = 'background: #f0f4f8; border-radius: 12px 12px 12px 4px; padding: 10px 14px; max-width: 85%; font-size: 13px; line-height: 1.5;';
    }
    div.innerHTML = text.replace(/\n/g, '<br>');
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
    return div;
}

function addTyping() {
    const container = document.getElementById('chat-messages');
    const div = document.createElement('div');
    div.id = 'typing-' + Date.now();
    div.style.cssText = 'background: #f0f4f8; border-radius: 12px 12px 12px 4px; padding: 10px 14px; max-width: 85%; font-size: 13px;';
    div.innerHTML = '<span style="display:inline-flex;gap:4px;"><span style="animation:bounce 0.6s infinite">●</span><span style="animation:bounce 0.6s infinite 0.2s">●</span><span style="animation:bounce 0.6s infinite 0.4s">●</span></span>';
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
    return div.id;
}

function removeTyping(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

// Initialize welcome message on page load
document.addEventListener('DOMContentLoaded', function() {
    addWelcomeMessage();
});
</script>