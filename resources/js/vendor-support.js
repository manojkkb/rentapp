import { io } from 'socket.io-client';

function formatTime(iso) {
    if (!iso) {
        return '';
    }
    try {
        const d = new Date(iso);
        return d.toLocaleString(undefined, {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return '';
    }
}

function vendorSupportChatFactory(config = {}) {
    return {
        messages: Array.isArray(config.messages) ? [...config.messages] : [],
        body: '',
        sending: false,
        connected: false,
        socketError: '',
        mobileTab: 'chat',
        ticketStatus: config.ticketStatus || 'open',
        socket: null,
        socketUrl: config.socketUrl || '',
        socketToken: config.socketToken || '',
        socketConfigured: config.socketConfigured !== false,
        sendUrl: config.sendUrl || '',
        freshConnectAttempted: false,

        init() {
            if (!this.socketConfigured || !this.socketUrl || !this.socketToken) {
                this.socketError = 'Live chat is not configured on the server.';
                return;
            }

            this.connectSocket();

            this.$nextTick(() => this.scrollToBottom());

            if (window.visualViewport) {
                window.visualViewport.addEventListener('resize', () => {
                    this.$nextTick(() => this.scrollToBottom());
                });
            }
        },

        isSessionUnknownError(err) {
            const message = err?.message || '';
            return message.includes('Session ID unknown') || err?.data?.code === 1;
        },

        connectSocket({ forceNew = true } = {}) {
            if (this.socket) {
                this.socket.removeAllListeners();
                this.socket.disconnect();
                this.socket = null;
            }

            const useHttps = this.socketUrl.startsWith('https://');

            this.socket = io(this.socketUrl, {
                auth: { token: this.socketToken },
                transports: useHttps ? ['websocket'] : ['polling', 'websocket'],
                forceNew,
                reconnection: true,
                reconnectionAttempts: 10,
                reconnectionDelay: 2000,
            });

            this.socket.on('connect', () => {
                this.connected = true;
                this.socketError = '';
                this.freshConnectAttempted = false;
            });

            this.socket.on('disconnect', () => {
                this.connected = false;
            });

            this.socket.on('connect_error', (err) => {
                this.connected = false;

                if (this.isSessionUnknownError(err) && !this.freshConnectAttempted) {
                    this.freshConnectAttempted = true;
                    this.connectSocket({ forceNew: true });
                    return;
                }

                this.socketError = err?.message || 'Could not connect to chat server.';
                console.error('Support socket connect_error:', this.socketError, err);
            });

            this.socket.on('new-message', (msg) => {
                this.appendMessage(msg);
                this.updateTicketStatusFromMessage(msg);
            });
        },

        onComposerFocus() {
            this.mobileTab = 'chat';
            setTimeout(() => this.scrollToBottom(), 300);
        },

        focusComposer() {
            this.$nextTick(() => {
                const input = this.$refs.messageInput;
                if (input && typeof input.focus === 'function') {
                    input.focus();
                }
            });
        },

        onEnterKey(event) {
            if (event.shiftKey) {
                return;
            }
            event.preventDefault();
            this.send();
        },

        updateTicketStatusFromMessage(msg) {
            if (!msg?.sender_type) {
                return;
            }
            this.ticketStatus = msg.sender_type === 'vendor' ? 'open' : 'closed';
        },

        appendMessage(msg) {
            if (!msg?.id) {
                return;
            }
            if (this.messages.some((m) => m.id === msg.id)) {
                return;
            }
            this.messages.push(msg);
            this.updateTicketStatusFromMessage(msg);
            this.$nextTick(() => this.scrollToBottom());
        },

        async send() {
            const text = this.body.trim();
            if (!text || this.sending || !this.sendUrl) {
                return;
            }

            this.sending = true;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const res = await fetch(this.sendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ body: text }),
                    credentials: 'same-origin',
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Failed to send');
                }

                this.body = '';
                this.appendMessage(data.message);
                if (data.ticket_status) {
                    this.ticketStatus = data.ticket_status;
                }
            } catch (e) {
                console.error(e);
                alert('Could not send message. Please try again.');
            } finally {
                this.sending = false;
                this.focusComposer();
            }
        },

        scrollToBottom() {
            const el = this.$refs.messageList;
            if (el) {
                el.scrollTop = el.scrollHeight;
            }
        },

        formatTime,
    };
}

function registerVendorSupportChat() {
    if (typeof window.Alpine?.data !== 'function') {
        return;
    }
    window.Alpine.data('vendorSupportChat', vendorSupportChatFactory);
}

document.addEventListener('alpine:init', registerVendorSupportChat);

registerVendorSupportChat();
