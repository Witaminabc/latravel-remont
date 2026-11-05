<template>
    <section id="lead-form" class="request-form">
        <div class="request-form__inner">
            <h2 class="request-form__title">{{ headingText }}</h2>
            <p v-if="subtitleText" class="request-form__subtitle">{{ subtitleText }}</p>
            <form class="request-form__form" @submit.prevent="submit">
                <div class="request-form__row">
                    <input v-model="form.name" class="request-form__input" type="text" :placeholder="L.name" required />
                    <input v-model="form.phone" class="request-form__input" type="tel" :placeholder="L.phone" required />
                </div>
                <input v-model="form.email" class="request-form__input" type="email" :placeholder="L.email" />
                <textarea v-model="form.message" class="request-form__input request-form__textarea" :placeholder="L.message" rows="3"></textarea>
                <button class="request-form__btn" type="submit" :disabled="loading">
                    {{ loading ? L.sending : buttonText }}
                </button>
                <p v-if="status === 'ok'" class="request-form__msg request-form__msg--ok">{{ L.ok }}</p>
                <p v-if="status === 'err'" class="request-form__msg request-form__msg--err">{{ L.err }}</p>
            </form>
        </div>
    </section>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
    heading: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    buttonLabel: { type: String, default: '' },
})

const L = {
    name: 'Ваше имя',
    phone: 'Телефон',
    email: 'E-mail',
    message: 'Сообщение',
    sending: 'Отправка...',
    ok: 'Заявка отправлена. Мы с вами свяжемся.',
    err: 'Не удалось отправить заявку. Попробуйте еще раз.',
}

const headingText = computed(() => props.heading || 'Обсудим ваш ремонт')
const subtitleText = computed(() => props.subtitle || '')
const buttonText = computed(() => props.buttonLabel || 'Отправить заявку')

const form = reactive({ name: '', phone: '', email: '', message: '' })
const loading = ref(false)
const status = ref('')

const submit = async () => {
    loading.value = true
    status.value = ''
    try {
        await axios.post('/api/leads', { ...form })
        status.value = 'ok'
        form.name = ''
        form.phone = ''
        form.email = ''
        form.message = ''
    } catch (e) {
        status.value = 'err'
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.request-form { padding: 56px 20px 80px; background: #f8fafc; }
.request-form__inner { max-width: 720px; margin: 0 auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 20px; padding: 32px; box-shadow: 0 16px 40px rgba(17,24,39,0.08); }
.request-form__title { margin: 0 0 8px; font-size: 2rem; text-align: center; color: #111827; }
.request-form__subtitle { margin: 0 0 24px; text-align: center; color: #4b5563; line-height: 1.6; }
.request-form__form { display: flex; flex-direction: column; gap: 14px; }
.request-form__row { display: flex; gap: 14px; flex-wrap: wrap; }
.request-form__row .request-form__input { flex: 1 1 200px; }
.request-form__input { width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 1rem; color: #111827; background: #fff; outline: none; }
.request-form__input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
.request-form__textarea { resize: vertical; min-height: 96px; }
.request-form__btn { padding: 14px 24px; border: 0; border-radius: 12px; background: #2563eb; color: #fff; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background .15s ease; }
.request-form__btn:hover { background: #1d4ed8; }
.request-form__btn:disabled { opacity: .6; cursor: default; }
.request-form__msg { margin: 4px 0 0; padding: 10px 12px; border-radius: 10px; font-size: .95rem; }
.request-form__msg--ok { background: #ecfdf5; color: #047857; }
.request-form__msg--err { background: #fef2f2; color: #b91c1c; }
@media (max-width: 560px) { .request-form { padding: 40px 16px 56px; } .request-form__inner { padding: 22px; } .request-form__title { font-size: 1.6rem; } }
</style>
