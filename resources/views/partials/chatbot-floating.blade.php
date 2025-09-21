@php
    $shouldRenderChatbot = ($chatbotSetting?->module_enabled ?? false) && filled($chatbotSetting?->endpoint);
    $chatbotTitle = $chatbotSetting->default_title ?? 'Gerindra Assistant';
    $chatbotButtonText = $chatbotSetting->chat_button_text ?? 'Tanya Sandra';
    $positionClass = $chatbotSetting->chat_button_position === 'bottom-left' ? 'left-6' : 'right-6';
    $avatarUrl = null;
    if (filled($chatbotSetting?->default_avatar_path)) {
        $avatarUrl = asset('storage/' . $chatbotSetting->default_avatar_path);
    }
@endphp

@once
    <script>
        (() => {
            const register = (Alpine) => {
                Alpine.data('chatbotWidget', (config) => ({
                open: false,
                sending: false,
                input: '',
                messages: [],
                endpoint: config.endpoint,
                session: config.session,
                title: config.title,
                buttonText: config.buttonText,
                csrf: config.csrf,
                storageStrategy: config.storageStrategy,
                storageKey: config.storageKey,
                storageTtl: config.storageTtl,
                avatar: config.avatar,

                init() {
                    this.loadHistory();
                },

                toggle() {
                    this.open = !this.open;
                    if (this.open) {
                        this.scrollToBottom();
                    }
                },

                async sendMessage() {
                    const trimmed = this.input.trim();
                    if (! trimmed || this.sending) {
                        return;
                    }

                    this.appendMessage('user', trimmed);
                    this.input = '';
                    this.sending = true;

                    try {
                        const response = await fetch(this.endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                message: trimmed,
                                history: this.serializableHistory(),
                                session: this.session,
                            }),
                        });

                        if (! response.ok) {
                            throw new Error('Invalid response');
                        }

                        const payload = await response.json();
                        const reply = this.extractReply(payload);
                        this.appendMessage('assistant', reply ?? 'Maaf, tidak ada respon.');
                    } catch (error) {
                        console.error(error);
                        this.appendMessage('system', 'Mohon maaf, terjadi kesalahan pada layanan chatbot.');
                    } finally {
                        this.sending = false;
                    }
                },

                appendMessage(role, content) {
                    const plain = this.toPlainText(content);
                    this.messages.push({
                        role,
                        content: plain,
                        display: this.renderDisplay(plain),
                        at: Date.now(),
                    });
                    this.trimHistory();
                    this.persistHistory();
                    this.scrollToBottom();
                },

                extractReply(payload) {
                    if (! payload) {
                        return null;
                    }

                    const sequences = [
                        payload.reply,
                        payload.answer,
                        payload.text,
                        payload.output,
                        payload.result?.answer,
                        payload.result?.output,
                        payload.result,
                        payload.data?.reply,
                    ];

                    for (const candidate of sequences) {
                        if (typeof candidate === 'string' && candidate.trim().length > 0) {
                            return candidate;
                        }
                    }

                    for (const candidate of sequences) {
                        if (candidate && typeof candidate === 'object') {
                            if (typeof candidate.output === 'string') {
                                return candidate.output;
                            }

                            try {
                                return this.toPlainText(candidate);
                            } catch (error) {
                                console.warn('Failed to normalize chatbot reply', error);
                            }
                        }
                    }

                    return null;
                },

                toPlainText(value) {
                    if (value == null) {
                        return '';
                    }

                    if (typeof value === 'string') {
                        return this.stripHtml(value);
                    }

                    if (Array.isArray(value)) {
                        return value.map((item) => this.toPlainText(item)).join('\n');
                    }

                    if (typeof value === 'object') {
                        const entries = Object.entries(value);
                        const meaningfulEntries = entries.filter(([, val]) => ! this.isEmptyValue(val));

                        if (meaningfulEntries.length === 1) {
                            const [onlyKey, onlyVal] = meaningfulEntries[0];
                            if (this.shouldDropKeyName(onlyKey)) {
                                return this.toPlainText(onlyVal);
                            }
                        }

                        return meaningfulEntries
                            .map(([key, val]) => {
                                const inner = this.toPlainText(val);
                                if (! inner) {
                                    return this.shouldDropKeyName(key) ? '' : key;
                                }

                                return this.shouldDropKeyName(key)
                                    ? inner
                                    : `${key}: ${inner}`;
                            })
                            .filter(Boolean)
                            .join('\n');
                    }

                    return String(value);
                },

                renderDisplay(text) {
                    let value = text ?? '';

                    if (typeof value === 'string' && this.looksLikeJson(value)) {
                        try {
                            value = this.toPlainText(JSON.parse(value));
                        } catch (error) {
                            // Ignore JSON parse errors and fall back to original string.
                        }
                    }

                    const cleaned = this.stripHtml(value ?? '');
                    return cleaned.replace(/\n/g, '<br />');
                },

                stripHtml(text) {
                    const container = document.createElement('div');
                    container.innerHTML = String(text ?? '');
                    return (container.textContent || container.innerText || '').trim();
                },

                isEmptyValue(value) {
                    if (value == null) {
                        return true;
                    }

                    if (typeof value === 'string') {
                        return value.trim().length === 0;
                    }

                    if (Array.isArray(value)) {
                        return value.every((item) => this.isEmptyValue(item));
                    }

                    if (typeof value === 'object') {
                        return Object.values(value).every((item) => this.isEmptyValue(item));
                    }

                    return false;
                },

                shouldDropKeyName(key) {
                    if (key == null) {
                        return false;
                    }

                    const normalized = String(key).toLowerCase();
                    return ['output', 'text', 'answer', 'result', 'reply', 'message', 'content', 'data'].includes(normalized);
                },

                looksLikeJson(text) {
                    return typeof text === 'string' && text.trim().length >= 2 && ['{', '['].includes(text.trim()[0]);
                },

                serializableHistory() {
                    return this.messages
                        .filter((item) => ['user', 'assistant'].includes(item.role))
                        .map(({ role, content }) => ({ role, content }));
                },

                trimHistory() {
                    if (this.messages.length > 30) {
                        this.messages = this.messages.slice(this.messages.length - 30);
                    }
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const box = this.$refs.messages;
                        if (box) {
                            box.scrollTop = box.scrollHeight;
                        }
                    });
                },

                qualifiedStorage() {
                    if (this.storageStrategy === 'none') {
                        return null;
                    }

                    return this.storageStrategy === 'session'
                        ? window.sessionStorage
                        : window.localStorage;
                },

                persistHistory() {
                    const storage = this.qualifiedStorage();
                    if (! storage) {
                        return;
                    }

                    const record = {
                        storedAt: Date.now(),
                        messages: this.messages,
                    };

                    storage.setItem(this.storageKey, JSON.stringify(record));
                },

                loadHistory() {
                    const storage = this.qualifiedStorage();
                    if (! storage) {
                        return;
                    }

                    const raw = storage.getItem(this.storageKey);
                    if (! raw) {
                        return;
                    }

                    try {
                        const record = JSON.parse(raw);
                        if (record && Array.isArray(record.messages)) {
                            if (this.storageStrategy === 'ttl' && this.storageTtl) {
                                const expiresAt = record.storedAt + (this.storageTtl * 60 * 1000);
                                if (Date.now() > expiresAt) {
                                    storage.removeItem(this.storageKey);
                                    return;
                                }
                            }

                            this.messages = (record.messages ?? []).map((message) => ({
                                ...message,
                                display: this.renderDisplay(message.content ?? ''),
                            }));
                        }
                    } catch (error) {
                        console.warn('Failed to parse chatbot history', error);
                        storage.removeItem(this.storageKey);
                    }
                },
                }));
            };

            if (window.Alpine) {
                register(window.Alpine);
            } else {
                document.addEventListener('alpine:init', () => register(window.Alpine));
            }
        })();
    </script>
@endonce

@if ($shouldRenderChatbot)
    <div
        x-data="chatbotWidget({
            endpoint: '{{ route('chatbot.message') }}',
            session: '{{ session()->getId() }}',
            title: @js($chatbotTitle),
            buttonText: @js($chatbotButtonText),
            csrf: document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') ?? '',
            storageStrategy: @js($chatbotSetting->history_storage ?? 'ttl'),
            storageKey: 'chatbot:history',
            storageTtl: {{ (int) ($chatbotSetting->history_ttl_minutes ?? 0) }},
            avatar: @js($avatarUrl),
        })"
        class="fixed bottom-6 {{ $positionClass }} z-40"
        x-cloak
    >
        <div class="flex flex-col items-end space-y-3">
            <div
                x-show="open"
                x-transition
                class="w-80 sm:w-96 rounded-2xl shadow-2xl border border-neutral-200 bg-white overflow-hidden"
            >
                <div class="bg-[#c81e12] text-white px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <template x-if="avatar">
                            <img :src="avatar" alt="Chatbot avatar" class="h-8 w-8 rounded-full object-cover" />
                        </template>
                        <span class="font-semibold text-sm" x-text="title"></span>
                    </div>
                    <button type="button" class="text-white/70 hover:text-white" @click="toggle">×</button>
                </div>

                <div class="p-4 space-y-3 max-h-80 overflow-y-auto" x-ref="messages">
                    <template x-for="(message, index) in messages" :key="index">
                        <div
                            class="flex"
                            :class="{
                                'justify-end': message.role === 'user',
                                'justify-start': message.role !== 'user'
                            }"
                        >
                            <div
                                class="max-w-[80%] px-3 py-2 rounded-2xl text-sm leading-relaxed"
                                :class="{
                                    'bg-[#c81e12] text-white rounded-br-sm shadow-sm': message.role === 'user',
                                    'bg-neutral-100 text-neutral-900 rounded-bl-sm border border-neutral-200': message.role === 'assistant',
                                    'bg-amber-100 text-amber-900 text-xs rounded-bl-sm border border-amber-200': message.role === 'system'
                                }"
                                x-html="message.display"
                            ></div>
                        </div>
                    </template>
                </div>

                <div class="border-t border-neutral-200">
                    <form class="flex items-start space-x-2 p-3" @submit.prevent="sendMessage">
                        <textarea
                            x-model="input"
                            placeholder="Tulis pertanyaan..."
                            class="flex-1 resize-none border border-neutral-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#c81e12]/60"
                            rows="2"
                            @keydown.enter.prevent="if (! event.shiftKey) { sendMessage(); }"
                        ></textarea>
                        <button
                            type="submit"
                            class="bg-[#c81e12] hover:bg-[#a4160d] text-white px-3 py-2 rounded-xl text-sm font-medium disabled:opacity-60"
                            :disabled="sending"
                        >
                            <span x-show="!sending">Kirim</span>
                            <span x-show="sending">Mengirim...</span>
                        </button>
                    </form>
                </div>
            </div>

            <button
                type="button"
                class="inline-flex items-center space-x-2 px-4 py-3 rounded-full shadow-xl bg-[#c81e12] hover:bg-[#a4160d] text-white font-semibold"
                @click="toggle"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path d="M2.25 12c0 4.97 4.78 9 10.68 9a11.8 11.8 0 0 0 3.92-.66.75.75 0 0 0 .35-1.2l-1.66-1.93a.75.75 0 0 1 .1-1.07 6.75 6.75 0 0 0 2.51-5.14c0-4.97-4.78-9-10.68-9S2.25 7.03 2.25 12Z" />
                </svg>
                <span x-text="buttonText"></span>
            </button>
        </div>
    </div>
@endif
