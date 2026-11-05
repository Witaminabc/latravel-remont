<template>
    <div class="cw">
        <transition name="cw-slide">
            <section v-if="open" class="cw-panel" role="dialog" aria-label="Чат с поддержкой">
                <header class="cw-panel__head">
                    <div>
                        <strong class="cw-panel__title">Чат с поддержкой</strong>
                        <span class="cw-panel__sub">Ответим в рабочее время</span>
                    </div>
                    <div class="cw-panel__head-actions">
                        <button v-if="authorized" type="button" class="cw-panel__logout" @click="logout">Выйти</button>
                        <button type="button" class="cw-panel__close" aria-label="Закрыть" @click="open = false">✕</button>
                    </div>
                </header>

                <!-- Гость: вход / регистрация -->
                <div v-if="!authorized" class="cw-panel__auth">
                    <div class="cw-auth__tabs">
                        <button
                            type="button"
                            class="cw-auth__tab"
                            :class="{ 'is-active': authMode === 'login' }"
                            @click="authMode = 'login'"
                        >Вход</button>
                        <button
                            type="button"
                            class="cw-auth__tab"
                            :class="{ 'is-active': authMode === 'register' }"
                            @click="authMode = 'register'"
                        >Регистрация</button>
                    </div>

                    <form class="cw-auth__form" @submit.prevent="submitAuth">
                        <label v-if="authMode === 'register'" class="cw-auth__field">
                            <span>Имя</span>
                            <input
                                v-model="authForm.name"
                                type="text"
                                autocomplete="name"
                                required
                                placeholder="Как к вам обращаться"
                            >
                        </label>

                        <label class="cw-auth__field">
                            <span>Email</span>
                            <input
                                v-model="authForm.email"
                                type="email"
                                autocomplete="email"
                                required
                                placeholder="you@example.com"
                            >
                        </label>

                        <label class="cw-auth__field">
                            <span>Пароль</span>
                            <input
                                v-model="authForm.password"
                                type="password"
                                :autocomplete="authMode === 'register' ? 'new-password' : 'current-password'"
                                required
                                placeholder="Минимум 6 символов"
                            >
                        </label>

                        <p v-if="authError" class="cw-auth__error">{{ authError }}</p>

                        <button
                            type="submit"
                            class="cw-auth__submit"
                            :disabled="authLoading"
                        >{{ authLoading ? 'Подождите…' : (authMode === 'register' ? 'Создать аккаунт' : 'Войти') }}</button>
                    </form>
                </div>

                <!-- Авторизован: переписка -->
                <template v-else>
                    <div ref="listEl" class="cw-panel__messages">
                        <p v-if="loading" class="cw-panel__hint">Загрузка…</p>
                        <p v-else-if="error" class="cw-panel__hint cw-panel__hint--error">{{ error }}</p>
                        <p v-else-if="!messages.length" class="cw-panel__hint">
                            Здравствуйте! Напишите первым — ответим на все вопросы.
                        </p>

                        <div
                            v-for="m in messages"
                            :key="m.id"
                            class="cw-msg"
                            :class="{ 'cw-msg--mine': isMine(m) }"
                        >
                            <p class="cw-msg__text">{{ m.message }}</p>
                            <time class="cw-msg__time">{{ fmtTime(m.created_at) }}</time>
                        </div>
                    </div>

                    <form class="cw-panel__form" @submit.prevent="send">
                        <textarea
                            v-model="draft"
                            class="cw-panel__input"
                            rows="1"
                            placeholder="Сообщение…"
                            :disabled="sending"
                            @keydown.enter.exact.prevent="send"
                        ></textarea>
                        <button
                            type="submit"
                            class="cw-panel__send"
                            :disabled="sending || !draft.trim()"
                            aria-label="Отправить"
                        >➤</button>
                    </form>
                </template>
            </section>
        </transition>

        <button
            type="button"
            class="cw-fab"
            :aria-expanded="open"
            aria-label="Открыть чат"
            @click="toggle"
        >
            <span class="cw-fab__icon" aria-hidden="true">💬</span>
            <span v-if="unread > 0 && !open" class="cw-fab__badge">{{ unread > 9 ? '9+' : unread }}</span>
        </button>
    </div>
</template>

<script setup>
/**
 * Публичный виджет чата (кнопка 💬 + панель переписки с админом).
 *
 * Использует API teo/chat: GET /api/messages, POST /api/send-message,
 * POST /api/messages/mark-read, GET /api/messages/unread-count.
 * Авторизация — Bearer-токен из localStorage ('token', ставит вход в wpadmin).
 * Реалтайм — простой поллинг (без Echo на публичной части).
 */
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import axios from 'axios'

const open = ref(false)
const loading = ref(false)
const sending = ref(false)
const error = ref('')
const draft = ref('')
const messages = ref([])
const unread = ref(0)
const me = ref(null)
const listEl = ref(null)

// Авторизация / регистрация
const authMode = ref('login') // 'login' | 'register'
const authForm = ref({ name: '', email: '', password: '' })
const authLoading = ref(false)
const authError = ref('')

let pollTimer = null

const token = () => localStorage.getItem('token')
const tokenVal = ref(token())
const authorized = computed(() => Boolean(tokenVal.value))

const http = axios.create({
    baseURL: '/api/',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
})

http.interceptors.request.use((config) => {
    const t = token()
    if (t) config.headers.Authorization = `Bearer ${t}`
    return config
})

http.interceptors.response.use(
    (res) => res,
    (err) => {
        // Токен истёк/отозван — сбрасываем в гостевой режим
        if (err?.response?.status === 401) {
            localStorage.removeItem('token')
            tokenVal.value = null
            me.value = null
        }
        return Promise.reject(err)
    },
)

function isMine(m) {
    return Boolean(me.value) && m?.from_user?.id === me.value.id
}

function fmtTime(value) {
    if (!value) return ''
    const d = new Date(String(value).replace(' ', 'T'))
    if (Number.isNaN(d.getTime())) return ''
    return d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
}

async function scrollBottom() {
    await nextTick()
    const el = listEl.value
    if (el) el.scrollTop = el.scrollHeight
}

async function fetchMe() {
    const { data } = await http.get('user')
    me.value = data?.data ?? data
}

async function fetchUnread() {
    if (!authorized.value) return
    try {
        const { data } = await http.get('messages/unread-count')
        unread.value = Number(data?.unread_count ?? 0)
    } catch { /* тихо: счётчик — не критично */ }
}

async function fetchMessages() {
    if (!authorized.value) return
    loading.value = true
    error.value = ''
    try {
        const { data } = await http.get('messages')
        messages.value = Array.isArray(data) ? data : (data?.data ?? [])
        await scrollBottom()
    } catch (e) {
        error.value = e?.response?.data?.message || 'Не удалось загрузить историю'
    } finally {
        loading.value = false
    }
}

async function markRead() {
    const ids = messages.value
        .filter((m) => !m.is_read && !isMine(m))
        .map((m) => m.id)
    if (!ids.length) return
    try {
        await http.post('messages/mark-read', { message_ids: ids })
        unread.value = 0
    } catch { /* noop */ }
}

async function send() {
    const text = draft.value.trim()
    if (!text || sending.value) return

    sending.value = true
    error.value = ''
    try {
        const { data } = await http.post('send-message', { message: text })
        messages.value = [...messages.value, data?.data ?? data]
        draft.value = ''
        await scrollBottom()
    } catch (e) {
        error.value = e?.response?.data?.error
            || e?.response?.data?.message
            || 'Не удалось отправить сообщение'
    } finally {
        sending.value = false
    }
}

async function toggle() {
    open.value = !open.value
    if (open.value && authorized.value) {
        if (!me.value) {
            try { await fetchMe() } catch { /* 401 обработан интерсептором */ }
        }
        await fetchMessages()
        await markRead()
    }
}

function startPolling() {
    stopPolling()
    pollTimer = setInterval(async () => {
        if (!authorized.value) return
        if (open.value) {
            await fetchMessages()
        } else {
            await fetchUnread()
        }
    }, 15000)
}

function stopPolling() {
    if (pollTimer) {
        clearInterval(pollTimer)
        pollTimer = null
    }
}

function firstErrorMessage(e) {
    const data = e?.response?.data
    const errs = data?.errors
    if (errs) {
        const first = Object.values(errs)[0]
        if (Array.isArray(first) && first.length) return first[0]
    }
    return data?.message || 'Что-то пошло не так. Попробуйте снова.'
}

async function submitAuth() {
    if (authLoading.value) return
    authLoading.value = true
    authError.value = ''
    try {
        const url = authMode.value === 'register' ? 'register' : 'login'
        const payload = authMode.value === 'register'
            ? { name: authForm.value.name, email: authForm.value.email, password: authForm.value.password }
            : { email: authForm.value.email, password: authForm.value.password }

        const { data } = await http.post(url, payload)

        localStorage.setItem('token', data.token)
        tokenVal.value = data.token
        me.value = data.user
        authForm.value = { name: '', email: '', password: '' }
        error.value = ''

        await fetchUnread()
        await fetchMessages()
        await markRead()
    } catch (e) {
        authError.value = firstErrorMessage(e)
    } finally {
        authLoading.value = false
    }
}

function logout() {
    localStorage.removeItem('token')
    tokenVal.value = null
    me.value = null
    messages.value = []
    unread.value = 0
    error.value = ''
    authError.value = ''
}

onMounted(() => {
    if (authorized.value) {
        fetchUnread()
    }
    startPolling()
})

onBeforeUnmount(stopPolling)
</script>

<style scoped>
.cw {
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 90;
    font-family: inherit;
}

/* Кнопка */
.cw-fab {
    position: relative;
    width: 56px;
    height: 56px;
    border: none;
    border-radius: 50%;
    background: #2563eb;
    color: #fff;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.35);
    transition: transform 0.15s ease, background 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cw-fab:hover {
    background: #1d4ed8;
    transform: translateY(-2px);
}

.cw-fab__icon {
    pointer-events: none;
}

.cw-fab__badge {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 20px;
    height: 20px;
    padding: 0 5px;
    border-radius: 999px;
    background: #ef4444;
    color: #fff;
    font-size: 0.7rem;
    font-weight: 700;
    line-height: 20px;
    text-align: center;
}

/* Панель */
.cw-panel {
    position: absolute;
    right: 0;
    bottom: 68px;
    width: 360px;
    max-width: calc(100vw - 40px);
    height: 480px;
    max-height: calc(100vh - 120px);
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

.cw-panel__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    background: #2563eb;
    color: #fff;
}

.cw-panel__title {
    display: block;
    font-size: 1rem;
}

.cw-panel__sub {
    display: block;
    margin-top: 2px;
    font-size: 0.8rem;
    opacity: 0.85;
}

.cw-panel__close {
    border: none;
    background: transparent;
    color: inherit;
    font-size: 1rem;
    cursor: pointer;
    opacity: 0.85;
}

.cw-panel__close:hover {
    opacity: 1;
}

.cw-panel__head-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.cw-panel__logout {
    border: 1px solid rgba(255, 255, 255, 0.4);
    background: transparent;
    color: #fff;
    font-size: 0.8rem;
    padding: 4px 10px;
    border-radius: 8px;
    cursor: pointer;
}

.cw-panel__logout:hover {
    background: rgba(255, 255, 255, 0.15);
}

.cw-panel__auth {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 20px;
    overflow-y: auto;
    color: #4b5563;
}

.cw-auth__tabs {
    display: flex;
    gap: 8px;
    padding: 4px;
    background: #f3f4f6;
    border-radius: 10px;
}

.cw-auth__tab {
    flex: 1;
    border: none;
    background: transparent;
    color: #4b5563;
    font: inherit;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
}

.cw-auth__tab.is-active {
    background: #2563eb;
    color: #fff;
}

.cw-auth__form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.cw-auth__field {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #6b7280;
}

.cw-auth__field input {
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 10px 12px;
    font: inherit;
    font-size: 0.95rem;
    font-weight: 400;
    color: #111827;
    max-height: none;
    width: 100%;
    box-sizing: border-box;
}

.cw-auth__field input:focus {
    outline: 2px solid #2563eb;
    outline-offset: -1px;
    border-color: #2563eb;
}

.cw-auth__error {
    margin: 0;
    padding: 8px 10px;
    border-radius: 8px;
    background: #fef2f2;
    color: #b91c1c;
    font-size: 0.85rem;
    font-weight: 500;
}

.cw-auth__submit {
    margin-top: 4px;
    border: none;
    border-radius: 10px;
    background: #2563eb;
    color: #fff;
    font: inherit;
    font-size: 0.95rem;
    font-weight: 600;
    padding: 11px 16px;
    cursor: pointer;
}

.cw-auth__submit:hover {
    background: #1d4ed8;
}

.cw-auth__submit:disabled {
    opacity: 0.6;
    cursor: default;
}

.cw-msg {
    align-self: flex-start;
    max-width: 85%;
}

.cw-msg--mine {
    align-self: flex-end;
}

.cw-msg__text {
    margin: 0;
    padding: 10px 14px;
    border-radius: 14px;
    background: #fff;
    border: 1px solid #e5e7eb;
    font-size: 0.95rem;
    line-height: 1.5;
    white-space: pre-wrap;
    word-break: break-word;
}

.cw-msg--mine .cw-msg__text {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.cw-msg__time {
    display: block;
    margin-top: 2px;
    padding: 0 4px;
    font-size: 0.7rem;
    color: #9ca3af;
}

.cw-msg--mine .cw-msg__time {
    text-align: right;
}

.cw-panel__messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #f9fafb;
}

.cw-panel__hint {
    margin: auto 0;
    color: #6b7280;
    font-size: 0.9rem;
    text-align: center;
}

.cw-panel__hint--error {
    color: #b91c1c;
}

.cw-panel__form {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    padding: 12px;
    border-top: 1px solid #e5e7eb;
    background: #fff;
}

.cw-panel__input {
    flex: 1;
    resize: none;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 10px 12px;
    font: inherit;
    font-size: 0.95rem;
    max-height: 96px;
}

.cw-panel__input:focus {
    outline: 2px solid #2563eb;
    outline-offset: -1px;
    border-color: #2563eb;
}

.cw-panel__send {
    width: 42px;
    height: 42px;
    border: none;
    border-radius: 10px;
    background: #2563eb;
    color: #fff;
    font-size: 1rem;
    cursor: pointer;
    flex-shrink: 0;
}

.cw-panel__send:disabled {
    opacity: 0.5;
    cursor: default;
}

.cw-panel__send:not(:disabled):hover {
    background: #1d4ed8;
}

/* Анимация */
.cw-slide-enter-active,
.cw-slide-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.cw-slide-enter-from,
.cw-slide-leave-to {
    opacity: 0;
    transform: translateY(8px);
}

@media (max-width: 480px) {
    .cw {
        right: 12px;
        bottom: 12px;
    }

    .cw-panel {
        height: min(480px, calc(100vh - 100px));
    }
}
</style>

