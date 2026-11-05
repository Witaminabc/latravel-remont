<!--<template>-->
<!--    <div class="login-container">-->
<!--        <div class="login-box">-->
<!--            <div class="login-header">-->
<!--                <img src="/images/logo.svg" alt="Logo" class="logo" />-->
<!--                <h2>Вход в админ-панель</h2>-->
<!--            </div>-->

<!--            <form @submit.prevent="login">-->
<!--                <div class="field mb-3">-->
<!--                    <label class="font-bold block mb-2">Email</label>-->
<!--                    <InputText v-model="form.email" type="email" class="w-full" required />-->
<!--                </div>-->

<!--                <div class="field mb-3">-->
<!--                    <label class="font-bold block mb-2">Пароль</label>-->
<!--                    <InputText v-model="form.password" type="password" class="w-full" required />-->
<!--                </div>-->

<!--                <Button type="submit" label="Войти" icon="pi pi-sign-in" class="w-full" :loading="loading" />-->

<!--                <div v-if="error" class="error-message mt-3">-->
<!--                    <small class="text-red-500">{{ error }}</small>-->
<!--                </div>-->
<!--            </form>-->
<!--        </div>-->
<!--    </div>-->
<!--</template>-->

<!--<script setup>-->
<!--import { ref } from 'vue'-->
<!--import { useRouter } from 'vue-router'-->
<!--import { useToast } from 'primevue/usetoast'-->
<!--import InputText from 'primevue/inputtext'-->
<!--import Button from 'primevue/button'-->
<!--import api, { getCsrfToken } from '../services/api'-->

<!--const router = useRouter()-->
<!--const toast = useToast()-->
<!--const loading = ref(false)-->
<!--const error = ref('')-->
<!--const form = ref({ email: '', password: '' })-->

<!--const login = async () => {-->
<!--    loading.value = true-->
<!--    error.value = ''-->

<!--    try {-->
<!--        await getCsrfToken()-->
<!--        const response = await api.post('/api/login', form.value)-->

<!--        if (response.data.token) {-->
<!--            localStorage.setItem('token', response.data.token)-->
<!--            localStorage.setItem('user_name', response.data.user.name)-->

<!--            toast.add({-->
<!--                severity: 'success',-->
<!--                summary: 'Успех',-->
<!--                detail: 'Добро пожаловать в админ-панель',-->
<!--                life: 3000-->
<!--            })-->

<!--            router.push('/admin')-->
<!--        } else {-->
<!--            error.value = 'Неверные учетные данные'-->
<!--        }-->
<!--    } catch (err) {-->
<!--        error.value = err.response?.data?.message || 'Ошибка входа'-->
<!--        toast.add({-->
<!--            severity: 'error',-->
<!--            summary: 'Ошибка',-->
<!--            detail: error.value,-->
<!--            life: 3000-->
<!--        })-->
<!--    } finally {-->
<!--        loading.value = false-->
<!--    }-->
<!--}-->
<!--</script>-->

<!--<style scoped>-->
<!--.login-container {-->
<!--    min-height: 100vh;-->
<!--    display: flex;-->
<!--    align-items: center;-->
<!--    justify-content: center;-->
<!--    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);-->
<!--}-->

<!--.login-box {-->
<!--    background: white;-->
<!--    border-radius: 16px;-->
<!--    padding: 40px;-->
<!--    width: 100%;-->
<!--    max-width: 400px;-->
<!--    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);-->
<!--}-->

<!--.login-header {-->
<!--    text-align: center;-->
<!--    margin-bottom: 32px;-->
<!--}-->

<!--.logo {-->
<!--    height: 60px;-->
<!--    margin-bottom: 16px;-->
<!--}-->

<!--.login-header h2 {-->
<!--    color: #333;-->
<!--    font-size: 24px;-->
<!--}-->

<!--.error-message {-->
<!--    text-align: center;-->
<!--}-->
<!--</style>-->
<template>
    <form @submit.prevent="handleLogin">
        <input v-model="email" type="email" placeholder="Email" autocomplete="username" required>
        <input v-model="password" type="password" placeholder="Пароль" autocomplete="current-password" required>
        <button type="submit">Войти</button>
        <p v-if="error" class="error">{{ error }}</p>
    </form>
</template>

<script setup>
import {ref} from 'vue'
import {useAuthStore} from '../../stores/auth'
import {useRouter} from 'vue-router'

const email = ref('')
const password = ref('')
const error = ref('')
const authStore = useAuthStore()
const router = useRouter()

const handleLogin = async () => {
    error.value = ''

    // store.login() сам ловит ошибку и возвращает { success, error } — исключения не бросает
    const result = await authStore.login(email.value, password.value)

    if (result.success) {
        localStorage.setItem('user_name', authStore.user?.name || 'Admin')
        await router.push({ name: 'dashboard' })
    } else {
        error.value = result.error || 'Ошибка входа'
    }
}
</script>
