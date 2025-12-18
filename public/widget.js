(function() {
    'use strict';

    const currentScript = document.currentScript;
    const chatId = currentScript?.getAttribute('data-chat-id');

    if (!chatId) {
        console.error('AI Chat Widget: data-chat-id не указан');
        return;
    }

    const apiBaseUrl = currentScript.src.replace('/widget.js', '').replace(/\/+$/, '');

    const storageKey = `ai_chat_session_${chatId}`;
    let sessionId = null;
    let isOpen = false;
    let isLoading = false;
    let chatConfig = null;
    let domainCheckPassed = false;

    function isAllowedDomain(allowedDomains) {
        if (!allowedDomains || allowedDomains.length === 0) {
            return true;
        }

        const currentHost = window.location.hostname;
        const currentPath = window.location.pathname;

        if (currentHost === apiBaseUrl.replace(/^https?:\/\//, '').split('/')[0]) {
            if (currentPath.includes('/demo')) {
                return true;
            }
        }

        for (const domain of allowedDomains) {
            if (currentHost === domain || currentHost.endsWith('.' + domain)) {
                return true;
            }
        }

        return false;
    }

    const savedSession = localStorage.getItem(storageKey);
    if (savedSession) {
        try {
            const sessionData = JSON.parse(savedSession);
            if (Date.now() - sessionData.timestamp < 30 * 24 * 60 * 60 * 1000) {
                sessionId = sessionData.sessionId;
            } else {
                localStorage.removeItem(storageKey);
            }
        } catch (e) {
            localStorage.removeItem(storageKey);
        }
    }

    const container = document.createElement('div');
    container.id = 'ai-chat-widget-container';
    const shadow = container.attachShadow({ mode: 'closed' });

    const styles = `
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        .widget-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            z-index: 2147483647;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .widget-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .widget-button svg {
            width: 28px;
            height: 28px;
            fill: white;
        }

        .widget-window {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 380px;
            height: 520px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            z-index: 2147483646;
            display: none;
            flex-direction: column;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }

        .widget-window.open {
            display: flex;
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .widget-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .widget-header h3 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .widget-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .widget-close:hover {
            opacity: 1;
        }

        .widget-close svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        .widget-messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .message {
            max-width: 85%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .message strong {
            font-weight: 600;
        }

        .message em {
            font-style: italic;
        }

        .message a {
            color: inherit;
            text-decoration: underline;
        }

        .message.user a {
            color: rgba(255, 255, 255, 0.95);
        }

        .message.assistant a {
            color: #667eea;
        }

        .message ul,
        .message ol {
            margin: 8px 0;
            padding-left: 20px;
        }

        .message li {
            margin: 4px 0;
        }

        .message.user {
            align-self: flex-end;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.assistant {
            align-self: flex-start;
            background: #f0f2f5;
            color: #1a1a1a;
            border-bottom-left-radius: 4px;
        }

        .message.welcome {
            align-self: center;
            background: #e8f4fd;
            color: #1976d2;
            text-align: center;
            font-size: 13px;
        }

        .typing-indicator {
            display: flex;
            gap: 4px;
            padding: 12px 16px;
            background: #f0f2f5;
            border-radius: 16px;
            border-bottom-left-radius: 4px;
            align-self: flex-start;
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            background: #667eea;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-8px);
            }
        }

        .widget-input-area {
            padding: 16px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 8px;
        }

        .widget-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .widget-input:focus {
            border-color: #667eea;
        }

        .widget-input::placeholder {
            color: #9ca3af;
        }

        .widget-send {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s, opacity 0.2s;
        }

        .widget-send:hover:not(:disabled) {
            transform: scale(1.05);
        }

        .widget-send:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .widget-send svg {
            width: 20px;
            height: 20px;
            fill: white;
        }

        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            text-align: center;
            margin: 0 16px;
        }

        @media (max-width: 480px) {
            .widget-window {
                width: calc(100vw - 20px);
                height: calc(100vh - 200px);
                bottom: 80px;
                right: 10px;
            }

            .widget-button {
                right: 10px;
                bottom: 10px;
            }
        }
    `;

    const html = `
        <style>${styles}</style>

        <button class="widget-button" id="widgetButton">
            <svg viewBox="0 0 24 24">
                <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
            </svg>
        </button>

        <div class="widget-window" id="widgetWindow">
            <div class="widget-header">
                <h3 id="chatTitle">Чат</h3>
                <button class="widget-close" id="closeButton">
                    <svg viewBox="0 0 24 24">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>
            <div class="widget-messages" id="messagesContainer">
                <div class="message welcome">Привет! Чем могу помочь?</div>
            </div>
            <div class="widget-input-area">
                <input type="text" class="widget-input" id="messageInput" placeholder="Введите сообщение..." />
                <button class="widget-send" id="sendButton">
                    <svg viewBox="0 0 24 24">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </div>
        </div>
    `;

    shadow.innerHTML = html;
    document.body.appendChild(container);

    const widgetButton = shadow.getElementById('widgetButton');
    const widgetWindow = shadow.getElementById('widgetWindow');
    const closeButton = shadow.getElementById('closeButton');
    const messagesContainer = shadow.getElementById('messagesContainer');
    const messageInput = shadow.getElementById('messageInput');
    const sendButton = shadow.getElementById('sendButton');
    const chatTitle = shadow.getElementById('chatTitle');

    async function apiRequest(endpoint, options = {}) {
        const url = `${apiBaseUrl}/api/widget/${chatId}${endpoint}`;

        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...options.headers,
            },
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Произошла ошибка');
        }

        return data;
    }

    async function loadHistory() {
        if (!sessionId) return;

        try {
            const response = await apiRequest(`/history?session_id=${sessionId}`);

            if (response.messages && response.messages.length > 0) {
                const welcomeMessage = messagesContainer.querySelector('.message.welcome');
                messagesContainer.innerHTML = '';
                if (welcomeMessage) {
                    messagesContainer.appendChild(welcomeMessage);
                }

                response.messages.forEach(msg => {
                    addMessage(msg.content, msg.role);
                });
            }
        } catch (error) {
            console.error('AI Chat Widget: Ошибка загрузки истории', error);
            sessionId = null;
            localStorage.removeItem(storageKey);
        }
    }

    async function initChat() {
        try {
            chatConfig = await apiRequest('/config');

            if (!isAllowedDomain(chatConfig.allowed_domains)) {
                console.warn('AI Chat Widget: Домен не разрешен для этого чат-бота');
                container.remove();
                return;
            }

            domainCheckPassed = true;
            chatTitle.textContent = chatConfig.name || 'Чат';

            if (sessionId) {
                await loadHistory();
                if (sessionId) {
                    return;
                }
            }

            const sessionData = await apiRequest('/session', { method: 'POST' });
            sessionId = sessionData.session_id;

            localStorage.setItem(storageKey, JSON.stringify({
                sessionId: sessionId,
                timestamp: Date.now(),
            }));

        } catch (error) {
            console.error('AI Chat Widget: Ошибка инициализации', error);
            showError('Не удалось подключиться к чату');
        }
    }

    function parseMarkdown(text) {
        let result = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        result = result.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
        result = result.replace(/\*\*\*([^*]+)\*\*\*/g, '<strong><em>$1</em></strong>');
        result = result.replace(/___([^_]+)___/g, '<strong><em>$1</em></strong>');
        result = result.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
        result = result.replace(/__([^_]+)__/g, '<strong>$1</strong>');
        result = result.replace(/\*([^*]+)\*/g, '<em>$1</em>');
        result = result.replace(/_([^_]+)_/g, '<em>$1</em>');

        const lines = result.split('\n');
        let inList = false;
        let listType = null;
        const processedLines = [];

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i];
            const ulMatch = line.match(/^[\s]*[-*]\s+(.+)$/);
            const olMatch = line.match(/^[\s]*\d+\.\s+(.+)$/);

            if (ulMatch) {
                if (!inList || listType !== 'ul') {
                    if (inList) processedLines.push(`</${listType}>`);
                    processedLines.push('<ul>');
                    listType = 'ul';
                    inList = true;
                }
                processedLines.push(`<li>${ulMatch[1]}</li>`);
            } else if (olMatch) {
                if (!inList || listType !== 'ol') {
                    if (inList) processedLines.push(`</${listType}>`);
                    processedLines.push('<ol>');
                    listType = 'ol';
                    inList = true;
                }
                processedLines.push(`<li>${olMatch[1]}</li>`);
            } else {
                if (inList && line.trim() === '') {
                    continue;
                }
                if (inList) {
                    processedLines.push(`</${listType}>`);
                    inList = false;
                    listType = null;
                }
                processedLines.push(line);
            }
        }

        if (inList) {
            processedLines.push(`</${listType}>`);
        }

        result = processedLines.join('\n');

        result = result.replace(/\n(?![<])/g, '<br>');

        return result;
    }

    function addMessage(content, role) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${role}`;

        if (role === 'assistant' || role === 'user') {
            messageDiv.innerHTML = parseMarkdown(content);
        } else {
            messageDiv.textContent = content;
        }

        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function showTypingIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'typing-indicator';
        indicator.id = 'typingIndicator';
        indicator.innerHTML = '<span></span><span></span><span></span>';
        messagesContainer.appendChild(indicator);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function hideTypingIndicator() {
        const indicator = shadow.getElementById('typingIndicator');
        if (indicator) {
            indicator.remove();
        }
    }

    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        messagesContainer.appendChild(errorDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        setTimeout(() => errorDiv.remove(), 5000);
    }

    async function sendMessage() {
        const message = messageInput.value.trim();

        if (!message || isLoading || !sessionId) {
            return;
        }

        isLoading = true;
        sendButton.disabled = true;
        messageInput.value = '';

        addMessage(message, 'user');

        showTypingIndicator();

        try {
            const response = await apiRequest('/message', {
                method: 'POST',
                body: JSON.stringify({
                    session_id: sessionId,
                    message: message,
                }),
            });

            localStorage.setItem(storageKey, JSON.stringify({
                sessionId: sessionId,
                timestamp: Date.now(),
            }));

            hideTypingIndicator();
            addMessage(response.message, 'assistant');

        } catch (error) {
            hideTypingIndicator();
            showError(error.message || 'Не удалось отправить сообщение');
        } finally {
            isLoading = false;
            sendButton.disabled = false;
            messageInput.focus();
        }
    }

    let chatInitialized = false;

    function toggleWidget() {
        isOpen = !isOpen;
        widgetWindow.classList.toggle('open', isOpen);

        if (isOpen && !chatInitialized) {
            chatInitialized = true;
            initChat();
        }

        if (isOpen) {
            messageInput.focus();
        }
    }

    widgetButton.addEventListener('click', toggleWidget);
    closeButton.addEventListener('click', toggleWidget);

    sendButton.addEventListener('click', sendMessage);

    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isOpen) {
            toggleWidget();
        }
    });

    (async function checkDomainOnLoad() {
        try {
            const config = await apiRequest('/config');
            if (!isAllowedDomain(config.allowed_domains)) {
                console.warn('AI Chat Widget: Домен не разрешен для этого чат-бота');
                container.remove();
            }
        } catch (error) {
            console.error('AI Chat Widget: Ошибка при проверке домена', error);
        }
    })();
})();
