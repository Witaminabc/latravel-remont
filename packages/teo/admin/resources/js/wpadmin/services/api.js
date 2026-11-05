import axios from 'axios'

const api = axios.create({
    baseURL: '/api/',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    },
    withCredentials: true,
    withXSRFToken: true
})

// Токен подставляем на КАЖДЫЙ запрос, а не один раз при импорте модуля.
// Раньше он читался на старте (до логина), поэтому оставался 'null'
// и все запросы вкладок уходили с 'Authorization: Bearer null' -> 401.
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // window.location.href = '/admin/login'
        }
        return Promise.reject(error)
    }
)

const csrfApi = axios.create({
    baseURL: '/',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
    },
    withCredentials: true,
    withXSRFToken: true
})

export const getCsrfToken = async () => {
    try {
        await csrfApi.get('/sanctum/csrf-cookie')
        return true
    } catch (error) {
        console.error('CSRF ошибка:', error)
        return false
    }
}

export default api
