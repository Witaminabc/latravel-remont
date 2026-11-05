import axios from 'axios'

/**
 * HTTP-клиент teo/chat.
 * Bearer-токен подставляется на каждый запрос из localStorage.
 */
const http = axios.create({
    baseURL: '/api/',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
})

http.interceptors.request.use(config => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

export default http