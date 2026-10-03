/**
 * File: public/js/ai-chat.js
 * Chat widget frontend "Pak Admin" — Vanilla JS
 * Config di-inject via window.AI_CHAT_CONFIG dari footer.php
 */
(function () {
    'use strict';

    const cfg = window.AI_CHAT_CONFIG;
    if (!cfg) return;

    // =========================================
    // SVG Icons (inline, tidak bergantung Lucide)
    // =========================================
    const ICONS = {
        messageCircle: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>',
        send: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>',
        x: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
        clock: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
        plus: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>',
        trash: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>',
        arrowLeft: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>',
        paperclip: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>',
        download: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
        file: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>',
    };

    // =========================================
    // State
    // =========================================
    let state = {
        isOpen: false,
        showHistory: false,
        activeConversationId: null,
        conversations: [],
        messages: [],
        isSending: false,
        historyOffset: 0,
        hasMoreHistory: false,
        pendingFile: null, // File object untuk attachment
    };

    // Restore state dari localStorage
    try {
        const saved = localStorage.getItem('ai_chat_state');
        if (saved) {
            const parsed = JSON.parse(saved);
            state.activeConversationId = parsed.activeConversationId || null;
        }
    } catch (e) { /* ignore */ }

    function saveState() {
        try {
            localStorage.setItem('ai_chat_state', JSON.stringify({
                activeConversationId: state.activeConversationId,
            }));
        } catch (e) { /* ignore */ }
    }

    // =========================================
    // API Helpers
    // =========================================
    async function apiGet(path) {
        const res = await fetch(cfg.baseurl + path, {
            credentials: 'same-origin',
        });
        return handleResponse(res);
    }

    async function apiPost(path, body, timeoutMs = 0) {
        const opts = {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': cfg.csrfToken,
            },
            body: JSON.stringify(body),
        };
        if (timeoutMs > 0) {
            const ac = new AbortController();
            opts.signal = ac.signal;
            setTimeout(() => ac.abort(), timeoutMs);
        }
        try {
            const res = await fetch(cfg.baseurl + path, opts);
            return handleResponse(res);
        } catch (e) {
            if (e.name === 'AbortError') return { success: false, error: 'Request timeout — proses AI terlalu lama. Coba lagi dengan pesan yang lebih pendek.' };
            throw e;
        }
    }

    async function apiPostFormData(path, formData, timeoutMs = 0) {
        formData.append('csrf_token', cfg.csrfToken);
        const opts = {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-Token': cfg.csrfToken,
            },
            body: formData,
        };
        if (timeoutMs > 0) {
            const ac = new AbortController();
            opts.signal = ac.signal;
            setTimeout(() => ac.abort(), timeoutMs);
        }
        try {
            const res = await fetch(cfg.baseurl + path, opts);
            return handleResponse(res);
        } catch (e) {
            if (e.name === 'AbortError') return { success: false, error: 'Request timeout — proses AI terlalu lama. Coba lagi dengan pesan yang lebih pendek.' };
            throw e;
        }
    }

    async function handleResponse(res) {
        // Session expired → redirect ke login
        if (res.redirected || res.url.includes('/auth/login')) {
            return { success: false, error: 'Sesi Anda berakhir. Silakan login ulang.', sessionExpired: true };
        }

        // CSRF gagal
        if (res.status === 419) {
            return { success: false, error: 'Sesi keamanan tidak valid. Muat ulang halaman.', csrfError: true };
        }

        // Fitur dimatikan
        if (res.status === 403) {
            return { success: false, error: 'Fitur AI Chat tidak aktif.', disabled: true };
        }

        // Rate limit
        if (res.status === 429) {
            const data = await res.json().catch(() => ({}));
            return { success: false, error: data.error || 'Batas pesan harian tercapai.', rateLimited: true };
        }

        // AI error
        if (res.status === 502 || res.status === 504) {
            const data = await res.json().catch(() => ({}));
            return { success: false, error: data.error || 'Server AI tidak merespons.', aiError: true };
        }

        // Server error (500, etc)
        if (res.status >= 500) {
            return { success: false, error: 'Terjadi kesalahan server. Coba lagi nanti.' };
        }

        try {
            return await res.json();
        } catch (e) {
            // Non-JSON response (HTML error page, etc)
            return { success: false, error: 'Terjadi kesalahan. Muat ulang halaman dan coba lagi.' };
        }
    }

    // =========================================
    // Markdown Renderer (aman — escape dulu)
    // =========================================
    function escapeHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Render ordered/unordered lists.
    // Mengelompokkan item bernomor berurutan menjadi SATU <ol> (agar nomor
    // 1,2,3,... benar), dan baris lanjutan (mis. "*(Kunci: ...)*") dianggap
    // bagian dari item sebelumnya walau dipisah baris kosong.
    function renderLists(html) {
        const lines = html.split('\n');
        const out = [];
        const isOrdered = (l) => /^\d+\.\s+/.test(l);
        const isBullet  = (l) => /^[\-\*]\s+/.test(l);
        const isBlockStart = (l) => /^<(?:h[1-4]|hr|pre|div|table|blockquote)\b/.test(l) || /^```/.test(l);

        let i = 0;
        while (i < lines.length) {
            const line = lines[i];
            const ordered = isOrdered(line);
            const bullet = !ordered && isBullet(line);
            if (!ordered && !bullet) { out.push(line); i++; continue; }

            const items = [];
            let cur = line.replace(/^(?:\d+\.|[\-\*])\s+/, '');
            i++;
            while (i < lines.length) {
                const l = lines[i];
                if (ordered ? isOrdered(l) : isBullet(l)) {
                    items.push(cur);
                    cur = l.replace(/^(?:\d+\.|[\-\*])\s+/, '');
                    i++;
                    continue;
                }
                if (l.trim() === '') {
                    // Lewati baris kosong hanya jika list masih berlanjut.
                    let j = i;
                    while (j < lines.length && lines[j].trim() === '') j++;
                    if (j < lines.length && (ordered ? isOrdered(lines[j]) : isBullet(lines[j]))) {
                        i = j;
                        continue;
                    }
                    break;
                }
                // Berhenti jika bertemu blok lain (heading/tabel/code/list lain).
                if (isBlockStart(l) || (ordered ? isBullet(l) : isOrdered(l))) break;
                // Baris lanjutan milik item saat ini.
                cur += '<br>' + l;
                i++;
            }
            items.push(cur);
            const tag = ordered ? 'ol' : 'ul';
            out.push('<' + tag + '>' + items.map(t => '<li>' + t + '</li>').join('') + '</' + tag + '>');
        }
        return out.join('\n');
    }

    function renderMarkdown(text) {
        let html = escapeHtml(text);

        // Code blocks (```)
        html = html.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
        // Inline code
        html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

        // Headings (h3, h2, h1) — harus sebelum bold/italic
        html = html.replace(/^#### (.+)$/gm, '<h4>$1</h4>');
        html = html.replace(/^### (.+)$/gm, '<h3>$1</h3>');
        html = html.replace(/^## (.+)$/gm, '<h3>$1</h3>');
        html = html.replace(/^# (.+)$/gm, '<h3>$1</h3>');

        // Horizontal rule
        html = html.replace(/^---+$/gm, '<hr>');
        html = html.replace(/^\*\*\*+$/gm, '<hr>');

        // Bold
        html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        // Italic (single * not preceded/followed by space for safety)
        html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');

        // Tables — detect header row + separator + data rows
        html = html.replace(/((?:^\|.+\|$\n?)+)/gm, function(tableBlock) {
            const rows = tableBlock.trim().split('\n').filter(r => r.trim());
            if (rows.length < 2) return tableBlock;

            // Check if second row is separator (| --- | --- |)
            const isSeparator = /^\|[\s:-]+\|/.test(rows[1]);
            if (!isSeparator) return tableBlock;

            let table = '<div class="ai-table-wrap"><table>';

            // Header row
            const headerCells = rows[0].split('|').filter((c, i, a) => i > 0 && i < a.length - 1);
            table += '<thead><tr>';
            headerCells.forEach(c => { table += '<th>' + c.trim() + '</th>'; });
            table += '</tr></thead>';

            // Data rows (skip separator at index 1)
            table += '<tbody>';
            for (let i = 2; i < rows.length; i++) {
                const cells = rows[i].split('|').filter((c, j, a) => j > 0 && j < a.length - 1);
                table += '<tr>';
                cells.forEach(c => { table += '<td>' + c.trim() + '</td>'; });
                table += '</tr>';
            }
            table += '</tbody></table></div>';
            return table;
        });

        // Lists (ordered & unordered) — dikelompokkan agar penomoran benar
        html = renderLists(html);

        // Paragraphs (double newline)
        html = html.replace(/\n\n/g, '</p><p>');
        // Single newline → <br> (but not after block elements)
        html = html.replace(/(?<!\>)\n(?!\<)/g, '<br>');
        // Wrap in paragraph
        html = '<p>' + html + '</p>';
        // Clean empty paragraphs
        html = html.replace(/<p>\s*<\/p>/g, '');
        // Don't wrap block elements in <p>
        html = html.replace(/<p>(<(?:h[1-4]|ul|ol|pre|hr|div|table))/g, '$1');
        html = html.replace(/(<\/(?:h[1-4]|ul|ol|pre|hr|div|table)>)<\/p>/g, '$1');

        return html;
    }

    // =========================================
    // Time formatting
    // =========================================
    function timeAgo(dateStr) {
        const date = new Date(dateStr);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000);

        if (diff < 60) return 'baru saja';
        if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
        if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
        if (diff < 604800) return Math.floor(diff / 86400) + ' hari lalu';
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    }

    // =========================================
    // DOM Builder
    // =========================================
    const root = document.getElementById('ai-chat-root');
    if (!root) return;

    // Pindahkan root ke <body> agar tidak terkurung stacking context
    // (mis. <main> dengan transform/animation) yang membuat z-index tidak berlaku.
    if (root.parentElement !== document.body) {
        document.body.appendChild(root);
    }

    // FAB Button
    const fab = document.createElement('button');
    fab.className = 'ai-chat-fab';
    fab.innerHTML = ICONS.messageCircle + '<span>' + escapeHtml(cfg.buttonLabel) + '</span>';
    fab.setAttribute('aria-label', cfg.buttonLabel);
    fab.addEventListener('click', toggleChat);
    root.appendChild(fab);

    // Chat Window
    const win = document.createElement('div');
    win.className = 'ai-chat-window';
    win.innerHTML = `
        <div class="ai-chat-header">
            <div class="ai-chat-header-avatar">🤖</div>
            <div class="ai-chat-header-info">
                <div class="ai-chat-header-name">${escapeHtml(cfg.aiName)}</div>
                <div class="ai-chat-header-status">Asisten AI</div>
            </div>
            <div class="ai-chat-header-actions">
                <button class="ai-chat-header-btn" data-action="history" title="Riwayat">${ICONS.clock}</button>
                <button class="ai-chat-header-btn" data-action="new" title="Percakapan Baru">${ICONS.plus}</button>
                <button class="ai-chat-header-btn" data-action="close" title="Tutup">${ICONS.x}</button>
            </div>
        </div>
        <div class="ai-chat-messages" id="ai-chat-messages"></div>
        <div class="ai-chat-file-preview" id="ai-chat-file-preview" style="display:none;"></div>
        <div class="ai-chat-input-area" id="ai-chat-input-area" style="display:none;">
            <button class="ai-chat-attach-btn" id="ai-chat-attach" title="Lampirkan file">${ICONS.paperclip}</button>
            <input type="file" id="ai-chat-file-input" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt" style="display:none;">
            <textarea class="ai-chat-input" id="ai-chat-input" placeholder="Ketik pesan..." rows="1"></textarea>
            <button class="ai-chat-send-btn" id="ai-chat-send" title="Kirim">${ICONS.send}</button>
        </div>
        <div class="ai-chat-history" id="ai-chat-history">
            <div class="ai-chat-history-header">
                <h4><span>🕘</span> Riwayat Percakapan</h4>
                <button class="ai-chat-header-btn" data-action="history-close" title="Kembali">${ICONS.x}</button>
            </div>
            <div class="ai-chat-history-list" id="ai-chat-history-list"></div>
        </div>
    `;
    root.appendChild(win);

    // References
    const messagesEl = win.querySelector('#ai-chat-messages');
    const inputArea = win.querySelector('#ai-chat-input-area');
    const inputEl = win.querySelector('#ai-chat-input');
    const sendBtn = win.querySelector('#ai-chat-send');
    const historyPanel = win.querySelector('#ai-chat-history');
    const historyList = win.querySelector('#ai-chat-history-list');
    const attachBtn = win.querySelector('#ai-chat-attach');
    const fileInput = win.querySelector('#ai-chat-file-input');
    const filePreview = win.querySelector('#ai-chat-file-preview');

    // =========================================
    // Event Listeners
    // =========================================
    // Header buttons
    win.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const action = btn.dataset.action;
            if (action === 'close') closeChat();
            else if (action === 'history') openHistory();
            else if (action === 'new') newConversation();
            else if (action === 'history-close') closeHistory();
        });
    });

    // Send message
    sendBtn.addEventListener('click', sendMessage);
    inputEl.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Auto-resize textarea
    inputEl.addEventListener('input', () => {
        inputEl.style.height = 'auto';
        inputEl.style.height = Math.min(inputEl.scrollHeight, 100) + 'px';
    });

    // File attachment
    attachBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (!file) return;

        // Validasi ukuran (10MB default)
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            showError('Ukuran file maksimal 10MB');
            fileInput.value = '';
            return;
        }

        state.pendingFile = file;
        showFilePreview(file);
    });

    function showFilePreview(file) {
        const isImage = file.type.startsWith('image/');
        const sizeStr = file.size < 1024 ? file.size + ' B'
            : file.size < 1048576 ? (file.size / 1024).toFixed(1) + ' KB'
            : (file.size / 1048576).toFixed(1) + ' MB';

        let previewHtml = '';
        if (isImage) {
            const url = URL.createObjectURL(file);
            previewHtml = `<img src="${url}" class="ai-chat-file-thumb" alt="preview">`;
        } else {
            previewHtml = `<div class="ai-chat-file-icon">${ICONS.file}</div>`;
        }

        filePreview.innerHTML = `
            ${previewHtml}
            <div class="ai-chat-file-info">
                <div class="ai-chat-file-name">${escapeHtml(file.name)}</div>
                <div class="ai-chat-file-size">${sizeStr}</div>
            </div>
            <button class="ai-chat-file-remove" title="Hapus">${ICONS.x}</button>
        `;
        filePreview.style.display = 'flex';

        filePreview.querySelector('.ai-chat-file-remove').addEventListener('click', clearFilePreview);
    }

    function clearFilePreview() {
        state.pendingFile = null;
        fileInput.value = '';
        filePreview.style.display = 'none';
        filePreview.innerHTML = '';
    }

    // =========================================
    // Core Functions
    // =========================================
    function toggleChat() {
        if (state.isOpen) closeChat();
        else openChat();
    }

    function openChat() {
        state.isOpen = true;
        win.classList.add('open');
        fab.classList.add('hidden');

        if (state.activeConversationId) {
            loadMessages(state.activeConversationId);
        } else {
            showWelcome();
        }
    }

    function closeChat() {
        state.isOpen = false;
        win.classList.remove('open');
        fab.classList.remove('hidden');
        closeHistory();
    }

    function showWelcome() {
        state.activeConversationId = null;
        saveState();
        inputArea.style.display = 'none';
        messagesEl.innerHTML = `
            <div class="ai-chat-welcome">
                <div class="ai-chat-welcome-icon">🤖</div>
                <h3>Halo, ${escapeHtml(cfg.userName)}!</h3>
                <p>Saya <strong>${escapeHtml(cfg.aiName)}</strong>, asisten AI Anda. Ada yang bisa saya bantu hari ini?</p>
                <button class="ai-chat-welcome-btn" onclick="document.querySelector('[data-action=new]').click()">
                    ${ICONS.plus} Buat Percakapan Baru
                </button>
            </div>
        `;
    }

    async function newConversation() {
        closeHistory();
        const res = await apiPost('/aichat/newConversation', {});

        if (res.disabled) {
            fab.classList.add('hidden');
            closeChat();
            return;
        }

        if (res.sessionExpired) {
            showSessionExpired();
            return;
        }

        if (!res.success) {
            showError(res.error || 'Gagal membuat percakapan baru');
            return;
        }

        state.activeConversationId = res.id_conversation;
        saveState();
        state.messages = [];
        renderMessages();
        inputArea.style.display = 'flex';
        inputEl.focus();
    }

    async function loadMessages(conversationId) {
        messagesEl.innerHTML = '<div class="ai-chat-typing"><div class="ai-chat-typing-dots"><span></span><span></span><span></span></div></div>';
        inputArea.style.display = 'none';

        const res = await apiGet('/aichat/messages/' + conversationId);

        if (!res.success) {
            if (res.sessionExpired) {
                showSessionExpired();
                return;
            }
            // Conversation mungkin sudah dihapus
            state.activeConversationId = null;
            saveState();
            showWelcome();
            return;
        }

        state.activeConversationId = conversationId;
        saveState();
        state.messages = res.messages || [];
        renderMessages();
        inputArea.style.display = 'flex';
        scrollToBottom();
    }

    function renderMessages() {
        if (state.messages.length === 0) {
            messagesEl.innerHTML = `
                <div class="ai-chat-msg assistant">
                    <div class="ai-chat-msg-avatar">🤖</div>
                    <div class="ai-chat-msg-bubble">
                        Halo <strong>${escapeHtml(cfg.userName)}</strong>! Ada yang bisa saya bantu?
                    </div>
                </div>
            `;
            return;
        }

        messagesEl.innerHTML = state.messages.map(msg => {
            const isUser = msg.role === 'user';
            const avatar = isUser ? '👤' : '🤖';
            const content = isUser ? escapeHtml(msg.content) : renderMarkdown(msg.content);

            // Render attachment
            let attachmentHtml = '';
            const att = msg.attachment ? (typeof msg.attachment === 'string' ? JSON.parse(msg.attachment) : msg.attachment) : null;

            if (isUser && att && att.type === 'upload') {
                attachmentHtml = renderUploadAttachment(att, msg);
            } else if (isUser && msg._localFile) {
                // Preview lokal sebelum server response
                attachmentHtml = renderLocalFilePreview(msg);
            } else if (!isUser && att && att.type === 'generated') {
                attachmentHtml = renderGeneratedFile(att);
            }

            return `
                <div class="ai-chat-msg ${msg.role}">
                    <div class="ai-chat-msg-avatar">${avatar}</div>
                    <div class="ai-chat-msg-bubble">
                        ${attachmentHtml}
                        ${content}
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderUploadAttachment(att, msg) {
        const mime = att.mime_type || '';
        if (mime.startsWith('image/')) {
            const url = att.download_url || '';
            return `<div class="ai-chat-img-bubble"><img src="${escapeHtml(url)}" alt="${escapeHtml(att.original_name || '')}" loading="lazy" onclick="window.open(this.src,'_blank')"></div>`;
        }
        return renderFileCard(att);
    }

    function renderLocalFilePreview(msg) {
        if (msg._localFileUrl) {
            return `<div class="ai-chat-img-bubble"><img src="${msg._localFileUrl}" alt="preview" loading="lazy"></div>`;
        }
        if (msg._localFile) {
            const sizeStr = formatFileSize(msg._localFile.size);
            return `<div class="ai-chat-file-card"><div class="ai-chat-file-card-icon">${ICONS.file}</div><div class="ai-chat-file-card-info"><div class="ai-chat-file-card-name">${escapeHtml(msg._localFile.name)}</div><div class="ai-chat-file-card-size">${sizeStr}</div></div></div>`;
        }
        return '';
    }

    function renderGeneratedFile(att) {
        return `<div class="ai-chat-file-card ai-chat-file-generated">
            <div class="ai-chat-file-card-icon">${ICONS.file}</div>
            <div class="ai-chat-file-card-info">
                <div class="ai-chat-file-card-name">${escapeHtml(att.original_name || att.filename || 'Dokumen')}</div>
                <div class="ai-chat-file-card-size">${formatFileSize(att.size || 0)}</div>
            </div>
            <a href="${escapeHtml(att.download_url || '')}" class="ai-chat-file-download" download title="Download">${ICONS.download}</a>
        </div>`;
    }

    function renderFileCard(att) {
        return `<div class="ai-chat-file-card">
            <div class="ai-chat-file-card-icon">${ICONS.file}</div>
            <div class="ai-chat-file-card-info">
                <div class="ai-chat-file-card-name">${escapeHtml(att.original_name || att.filename || 'File')}</div>
                <div class="ai-chat-file-card-size">${formatFileSize(att.size || 0)}</div>
            </div>
            <a href="${escapeHtml(att.download_url || '')}" class="ai-chat-file-download" download title="Download">${ICONS.download}</a>
        </div>`;
    }

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    async function sendMessage() {
        if (state.isSending) return;

        const message = inputEl.value.trim();
        if (!message) return;
        if (!state.activeConversationId) return;

        const pendingFile = state.pendingFile;

        state.isSending = true;
        sendBtn.disabled = true;
        inputEl.disabled = true;
        inputEl.value = '';
        inputEl.style.height = 'auto';
        clearFilePreview();

        // Tampilkan pesan user langsung (dengan attachment preview jika ada)
        const userMsg = { role: 'user', content: message };
        if (pendingFile) {
            userMsg._localFile = pendingFile;
            userMsg._localFileUrl = pendingFile.type.startsWith('image/') ? URL.createObjectURL(pendingFile) : null;
        }
        state.messages.push(userMsg);
        renderMessages();
        scrollToBottom();

        // Tampilkan typing indicator
        const typingEl = document.createElement('div');
        typingEl.className = 'ai-chat-typing';
        typingEl.innerHTML = '<div class="ai-chat-msg-avatar">🤖</div><div class="ai-chat-typing-dots"><span></span><span></span><span></span></div>';
        messagesEl.appendChild(typingEl);
        scrollToBottom();

        let res;
        if (pendingFile) {
            // Kirim dengan FormData (multipart)
            const formData = new FormData();
            formData.append('conversation_id', state.activeConversationId);
            formData.append('message', message);
            formData.append('attachment', pendingFile);
            res = await apiPostFormData('/aichat/send', formData, 330000);
        } else {
            // Kirim JSON biasa (timeout 330 detik)
            res = await apiPost('/aichat/send', {
                conversation_id: state.activeConversationId,
                message: message,
            }, 330000);
        }

        // Hapus typing indicator
        if (typingEl.parentNode) typingEl.remove();

        state.isSending = false;
        sendBtn.disabled = false;
        inputEl.disabled = false;
        inputEl.focus();

        if (res.sessionExpired) {
            showSessionExpired();
            return;
        }

        if (res.csrfError) {
            showError('Sesi keamanan tidak valid. Muat ulang halaman.');
            return;
        }

        if (res.disabled) {
            fab.classList.add('hidden');
            closeChat();
            return;
        }

        if (!res.success) {
            showErrorWithRetry(res.error || 'Gagal mendapatkan respons', message);
            return;
        }

        // Update user message dengan attachment info dari server
        if (res.attachment) {
            const lastUserMsg = state.messages.filter(m => m.role === 'user').pop();
            if (lastUserMsg) {
                lastUserMsg.attachment = res.attachment;
                delete lastUserMsg._localFile;
                delete lastUserMsg._localFileUrl;
            }
        }

        // Tampilkan respons AI
        const aiMsg = { role: 'assistant', content: res.reply };
        if (res.generated_files && res.generated_files.length > 0) {
            aiMsg.attachment = res.generated_files[0];
        }
        state.messages.push(aiMsg);
        renderMessages();
        scrollToBottom();

        // Update judul conversation jika AI generate judul baru
        if (res.title) {
            // Update di state conversations jika ada
            const conv = state.conversations.find(c => c.id == state.activeConversationId);
            if (conv) conv.judul = res.title;
        }
    }

    function showError(msg) {
        const el = document.createElement('div');
        el.className = 'ai-chat-error';
        el.innerHTML = '⚠️ ' + escapeHtml(msg);
        messagesEl.appendChild(el);
        scrollToBottom();
    }

    function showErrorWithRetry(msg, originalMessage) {
        const el = document.createElement('div');
        el.className = 'ai-chat-error';
        el.innerHTML = '⚠️ <span>' + escapeHtml(msg) + '</span>';

        const retryBtn = document.createElement('button');
        retryBtn.className = 'ai-chat-error-retry';
        retryBtn.textContent = 'Coba lagi';
        retryBtn.addEventListener('click', () => {
            el.remove();
            // Hapus pesan user terakhir dari state (akan dikirim ulang)
            if (state.messages.length > 0 && state.messages[state.messages.length - 1].role === 'user') {
                state.messages.pop();
            }
            inputEl.value = originalMessage;
            sendMessage();
        });
        el.appendChild(retryBtn);

        messagesEl.appendChild(el);
        scrollToBottom();
    }

    function showSessionExpired() {
        messagesEl.innerHTML = `
            <div class="ai-chat-welcome">
                <div class="ai-chat-welcome-icon">🔒</div>
                <h3>Sesi Berakhir</h3>
                <p>Sesi Anda telah berakhir. Silakan login ulang untuk melanjutkan.</p>
                <button class="ai-chat-welcome-btn" onclick="location.reload()">
                    Muat Ulang Halaman
                </button>
            </div>
        `;
        inputArea.style.display = 'none';
    }

    function scrollToBottom() {
        requestAnimationFrame(() => {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        });
    }

    // =========================================
    // History Panel
    // =========================================
    async function openHistory() {
        state.showHistory = true;
        state.historyOffset = 0;
        historyPanel.classList.add('open');
        await loadHistory();
    }

    function closeHistory() {
        state.showHistory = false;
        historyPanel.classList.remove('open');
    }

    async function loadHistory(append) {
        if (!append) {
            historyList.innerHTML = '<div style="padding:2rem;text-align:center;color:#94a3b8;"><div class="ai-chat-typing-dots" style="display:inline-flex;"><span></span><span></span><span></span></div></div>';
        }

        const res = await apiGet('/aichat/conversations?offset=' + state.historyOffset + '&limit=50');

        if (!res.success) {
            historyList.innerHTML = '<div class="ai-chat-history-empty"><p>Gagal memuat riwayat</p></div>';
            return;
        }

        const convs = res.conversations || [];
        state.hasMoreHistory = convs.length >= 50;

        if (!append) {
            historyList.innerHTML = '';
        } else {
            // Hapus tombol "Muat lagi" sebelumnya
            const oldBtn = historyList.querySelector('.ai-chat-load-more');
            if (oldBtn) oldBtn.remove();
        }

        if (convs.length === 0 && state.historyOffset === 0) {
            historyList.innerHTML = `
                <div class="ai-chat-history-empty">
                    <div style="font-size:2rem;">💬</div>
                    <p>Belum ada percakapan</p>
                </div>
            `;
            return;
        }

        convs.forEach(conv => {
            const item = document.createElement('div');
            item.className = 'ai-chat-history-item';
            item.innerHTML = `
                <div class="ai-chat-history-item-icon">💬</div>
                <div class="ai-chat-history-item-info">
                    <div class="ai-chat-history-item-title">${escapeHtml(conv.judul || 'Percakapan Baru')}</div>
                    <div class="ai-chat-history-item-meta">${conv.jumlah_pesan || 0} pesan · ${timeAgo(conv.updated_at)}</div>
                </div>
            `;

            // Klik untuk buka conversation
            item.addEventListener('click', (e) => {
                if (e.target.closest('.ai-chat-history-delete')) return;
                closeHistory();
                loadMessages(conv.id);
            });

            // Tombol hapus
            const delBtn = document.createElement('button');
            delBtn.className = 'ai-chat-history-delete';
            delBtn.innerHTML = ICONS.trash;
            delBtn.title = 'Hapus';
            delBtn.addEventListener('click', async (e) => {
                e.stopPropagation();
                if (!confirm('Hapus percakapan "' + (conv.judul || 'Percakapan Baru') + '"?')) return;

                const res = await apiPost('/aichat/deleteConversation/' + conv.id, {});
                if (res.success) {
                    item.remove();
                    if (state.activeConversationId == conv.id) {
                        state.activeConversationId = null;
                        saveState();
                        showWelcome();
                    }
                    // Cek apakah list kosong
                    if (!historyList.querySelector('.ai-chat-history-item')) {
                        historyList.innerHTML = '<div class="ai-chat-history-empty"><div style="font-size:2rem;">💬</div><p>Belum ada percakapan</p></div>';
                    }
                }
            });
            item.appendChild(delBtn);

            historyList.appendChild(item);
        });

        // Tombol "Muat lagi"
        if (state.hasMoreHistory) {
            const loadMoreBtn = document.createElement('button');
            loadMoreBtn.className = 'ai-chat-load-more';
            loadMoreBtn.textContent = 'Muat lagi...';
            loadMoreBtn.addEventListener('click', () => {
                state.historyOffset += 50;
                loadHistory(true);
            });
            historyList.appendChild(loadMoreBtn);
        }
    }

    // =========================================
    // Keyboard shortcut: Escape to close
    // =========================================
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (state.showHistory) closeHistory();
            else if (state.isOpen) closeChat();
        }
    });

})();
