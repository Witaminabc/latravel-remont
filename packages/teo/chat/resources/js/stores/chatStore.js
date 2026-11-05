import { defineStore } from 'pinia'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import http from '../http'
import { chatConfig, adminUrl } from '../config'

// Единственный Echo-инстанс пакета (защита от двойного подключения)
let echoInstance = null

export const useChatStore = defineStore('teo-chat', {
    state: () => ({
        me: null,
        stats: null,
        threads: [],            // [{ user, last_message, unread }]
        activeUserId: null,
        messages: {},           // { [userId]: Message[] } (по возрастанию)
        cursors: {},            // { [userId]: next_cursor | null }
        loadingThreads: false,
        loadingMessages: false,
        sending: false,
        initialized: false,
        connecting: false,
        connected: false,
        error: null,
        search: '',
        notifications: [],      // входящие для тостов (consumed во view)
    }),

    getters: {
        activeThread: (state) =>
            state.threads.find(t => t.user.id === state.activeUserId) || null,

        activePeer() {
            return this.activeThread?.user || null
        },

        activeMessages: (state) =>
            state.messages[state.activeUserId] || [],

        unreadTotal: (state) =>
            state.threads.reduce((sum, t) => sum + (t.unread || 0), 0),

        filteredThreads(state) {
            const q = state.search.trim().toLowerCase()
            if (!q) return state.threads
            return state.threads.filter(t =>
                (t.user.name || '').toLowerCase().includes(q)
                || (t.user.email || '').toLowerCase().includes(q)
            )
        },
    },

    actions: {
        /** Первичная инициализация: me + статистика + треды + Echo. Идемпотентна. */
        async init() {
            if (this.initialized || this.connecting) return
            const token = localStorage.getItem('token')
            if (!token) return

            this.connecting = true
            try {
                const { data } = await http.get(adminUrl('me'))
                this.me = data.data
                this.initialized = true
                this.error = null

                await Promise.all([this.fetchStats(), this.fetchThreads()])
                this.connectEcho()
            } catch (e) {
                this.error = e?.response?.data?.message || 'Не удалось загрузить чат'
            } finally {
                this.connecting = false
            }
        },

        /** Отключение (logout): закрыть Echo и сбросить состояние. */
        disconnect() {
            if (echoInstance) {
                try { echoInstance.disconnect() } catch { /* noop */ }
                echoInstance = null
            }
            this.$reset()
        },

        async fetchStats() {
            try {
                const { data } = await http.get(adminUrl('stats'))
                this.stats = data.data
            } catch (e) {
                this.error = e?.response?.data?.message || 'Ошибка статистики'
            }
        },

        async fetchThreads() {
            this.loadingThreads = true
            try {
                const { data } = await http.get(adminUrl('threads'))
                this.threads = data.data
            } catch (e) {
                this.error = e?.response?.data?.message || 'Ошибка списка диалогов'
            } finally {
                this.loadingThreads = false
            }
        },

        /** Открыть диалог: подгрузить историю и пометить прочитанным. */
        async openThread(userId) {
            const id = Number(userId)
            if (!id) return
            this.activeUserId = id

            await this.fetchMessages(id, 'initial')

            const thread = this.threads.find(t => t.user.id === id)
            if (thread && thread.unread > 0) {
                await this.markRead(id)
            }
        },

        async fetchMessages(userId, mode = 'initial') {
            this.loadingMessages = true
            try {
                if (mode === 'initial') {
                    const { data } = await http.get(
                        `${adminUrl(`threads/${userId}/messages`)}?limit=50`
                    )
                    this.messages[userId] = data.data
                    this.cursors[userId] = data.next_cursor
                } else {
                    const cursor = this.cursors[userId]
                    if (!cursor) return

                    const { data } = await http.get(
                        `${adminUrl(`threads/${userId}/messages`)}?limit=50&cursor=${cursor}`
                    )
                    const existing = new Set((this.messages[userId] || []).map(m => m.id))
                    const older = data.data.filter(m => !existing.has(m.id))
                    this.messages[userId] = [...older, ...(this.messages[userId] || [])]
                    this.cursors[userId] = data.next_cursor
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Ошибка загрузки сообщений'
            } finally {
                this.loadingMessages = false
            }
        },

        /** Пометить входящие от пользователя прочитанными. */
        async markRead(userId) {
            const thread = this.threads.find(t => t.user.id === userId)
            if (!thread || !thread.unread) return

            thread.unread = 0
            if (thread.last_message?.to_user?.id === this.me?.id) {
                thread.last_message = { ...thread.last_message, is_read: true }
            }
            const list = this.messages[userId] || []
            this.messages[userId] = list.map(m =>
                m.to_user?.id === this.me?.id ? { ...m, is_read: true } : m
            )

            try {
                await http.post(adminUrl(`threads/${userId}/read`))
                this.fetchStats()
            } catch {
                // локальное состояние уже обновлено
            }
        },

        /**
         * Оптимистичная отправка: сообщение появляется сразу с pending,
         * затем подменяется серверным (по id) — дублей нет.
         */
        async sendMessage(text) {
            const userId = this.activeUserId
            const trimmed = String(text || '').trim()
            if (!userId || !trimmed || this.sending) return null

            const tempId = `tmp-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`
            const optimistic = {
                id: tempId,
                message: trimmed,
                is_read: false,
                pending: true,
                failed: false,
                created_at: new Date().toISOString(),
                from_user: { id: this.me?.id, name: this.me?.name },
                to_user: { id: userId, name: this.activePeer?.name },
            }
            this.messages[userId] = [...(this.messages[userId] || []), optimistic]

            this.sending = true
            try {
                const { data } = await http.post(
                    adminUrl(`threads/${userId}/messages`),
                    { message: trimmed }
                )
                this.replaceMessage(userId, tempId, data.data)
                this.touchThread(userId, data.data)
                return data.data
            } catch (e) {
                this.markFailed(userId, tempId)
                this.error = e?.response?.data?.message
                    || (e?.response?.status === 422
                        ? 'Сообщение слишком длинное'
                        : 'Не удалось отправить сообщение')
                throw e
            } finally {
                this.sending = false
            }
        },

        /** Повтор неудавшейся отправки. */
        async retryMessage(userId, messageId) {
            const list = this.messages[userId] || []
            const failed = list.find(m => m.id === messageId && m.failed)
            if (!failed) return

            failed.pending = true
            failed.failed = false
            this.messages[userId] = [...list]

            try {
                const { data } = await http.post(
                    adminUrl(`threads/${userId}/messages`),
                    { message: failed.message }
                )
                this.replaceMessage(userId, messageId, data.data)
                this.touchThread(userId, data.data)
            } catch {
                this.markFailed(userId, messageId)
            }
        },

        markFailed(userId, messageId) {
            const list = this.messages[userId] || []
            const m = list.find(x => x.id === messageId)
            if (m) {
                m.pending = false
                m.failed = true
                this.messages[userId] = [...list]
            }
        },

        /** Подменить временное сообщение серверным (или добавить, если нет). */
        replaceMessage(userId, tempId, real) {
            const list = [...(this.messages[userId] || [])]
            const idx = list.findIndex(m => m.id === tempId)
            if (idx !== -1) {
                list[idx] = real
            } else if (!list.some(m => m.id === real.id)) {
                list.push(real)
                list.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
            }
            this.messages[userId] = list
        },

        /** Обновить последнее сообщение в списке тредов. */
        touchThread(userId, message) {
            const t = this.threads.find(x => x.user.id === userId)
            if (t) {
                t.last_message = message
                this.threads = [...this.threads].sort((a, b) =>
                    String(b.last_message?.created_at || '')
                        .localeCompare(String(a.last_message?.created_at || ''))
                )
            } else {
                // новый диалог — подтянуть список с сервера
                this.fetchThreads()
            }
        },

        async toggleBlock(userId, isBlocked) {
            const { data } = await http.post(adminUrl(`users/${userId}/block`), {
                is_blocked: isBlocked,
            })
            const t = this.threads.find(x => x.user.id === userId)
            if (t) t.user.is_blocked = data.data.is_blocked
            return data.data.is_blocked
        },

        async clearThread(userId) {
            await http.delete(adminUrl(`threads/${userId}`))
            this.messages[userId] = []
            this.cursors[userId] = null
            this.threads = this.threads.filter(t => t.user.id !== userId)
            this.fetchStats()
        },

        // ---------------------------------------------------------------
        // Обработчики broadcast-событий (Echo)
        // ---------------------------------------------------------------

        /** Входящее/исходящее сообщение через WebSocket (.MessageSent). */
        handleMessageSent(payload) {
            if (!payload?.id || !this.me) return

            // Echo/Reverb отдаёт id как строки — нормализуем весь пейлоад к числу,
            // иначе строгие сравнения с me.id (число) ломаются: своё эхо уходит
            // в тосты, а дедуп/read-статус могут расходиться.
            payload = {
                ...payload,
                id: Number(payload.id),
                from_user: payload.from_user ? { ...payload.from_user, id: Number(payload.from_user.id) } : null,
                to_user: payload.to_user ? { ...payload.to_user, id: Number(payload.to_user.id) } : null,
            }

            const meId = Number(this.me.id)
            const fromId = payload.from_user?.id ?? null
            const toId = payload.to_user?.id ?? null
            const peerId = fromId === meId ? toId : fromId
            if (peerId == null) return

            const list = this.messages[peerId]
            if (list) {
                // Уже есть (REST-ответ обогнал WS) — не дублируем
                if (list.some(m => m.id === payload.id)) {
                    this.touchThread(peerId, payload)
                    return
                }
                const next = [...list]
                // Подменяем оптимистичное сообщение, если REST ещё не вернулся
                const pendingIdx = next.findIndex(m =>
                    m.pending
                    && String(m.id).startsWith('tmp-')
                    && m.message === payload.message
                )
                if (pendingIdx !== -1) {
                    next[pendingIdx] = payload
                } else {
                    next.push(payload)
                }
                next.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
                this.messages[peerId] = next
            }

            this.touchThread(peerId, payload)

            if (fromId === meId) {
                return // своё же сообщение (эхо для мульти-вкладок)
            }

            const thread = this.threads.find(t => t.user.id === peerId)
            if (this.activeUserId === peerId && !document.hidden) {
                // Диалог открыт — читаем сразу
                this.markRead(peerId)
            } else {
                if (thread) {
                    thread.unread = (thread.unread || 0) + 1
                }
                if (this.stats) {
                    this.stats.unread_messages = (this.stats.unread_messages || 0) + 1
                }
                this.notifications.push({
                    id: payload.id,
                    from: payload.from_user,
                    text: payload.message,
                    at: Date.now(),
                })
                if (this.notifications.length > 20) this.notifications.shift()
            }

            // Новый собеседник — обновить список тредов
            if (!thread) {
                this.fetchThreads()
            }
        },

        /** Собеседник прочитал наши сообщения (.MessagesRead). */
        handleMessagesRead(payload) {
            const readerId = payload?.reader_id
            if (!readerId || !this.me) return

            // В треде с readerId исходящие (to_user = reader) → прочитаны
            const list = this.messages[readerId]
            if (list) {
                this.messages[readerId] = list.map(m =>
                    m.to_user?.id === readerId ? { ...m, is_read: true } : m
                )
            }

            const thread = this.threads.find(t => t.user.id === readerId)
            if (thread?.last_message?.to_user?.id === readerId) {
                thread.last_message = { ...thread.last_message, is_read: true }
            }
        },

        /** Подключение к private-каналу user.{me.id} через Reverb. */
        async connectEcho() {
            if (echoInstance || !this.me) return

            const token = localStorage.getItem('token')
            if (!token) return

            window.Pusher = window.Pusher || Pusher

            const cfg = chatConfig.echo || {}
            const env = import.meta.env

            const echo = new Echo({
                broadcaster: 'reverb',
                key: cfg.key ?? env.VITE_REVERB_APP_KEY,
                wsHost: cfg.wsHost ?? env.VITE_REVERB_HOST ?? window.location.hostname,
                wsPort: cfg.wsPort ?? Number(env.VITE_REVERB_PORT) ?? 8080,
                wssPort: cfg.wssPort ?? Number(env.VITE_REVERB_PORT) ?? 8080,
                forceTLS: cfg.forceTLS ?? (env.VITE_REVERB_SCHEME === 'https'),
                enabledTransports: ['ws', 'wss'],
                authEndpoint: chatConfig.authEndpoint,
                auth: {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`,
                    },
                },
            })

            echoInstance = echo

            echo.private(`user.${this.me.id}`)
                .subscribed(() => {
                    this.connected = true
                })
                .error((error) => {
                    this.connected = false
                    console.error('[teo/chat] channel error:', error)
                })
                .listen('.MessageSent', (payload) => this.handleMessageSent(payload))
                .listen('.MessagesRead', (payload) => this.handleMessagesRead(payload))
        },
    },
})

