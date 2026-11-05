// import { defineStore } from 'pinia'
// import { ref } from 'vue'
// import axios from 'axios'
//
// export const useAuthStore = defineStore('auth', () => {
//     const user = ref(null)
//     const token = ref(localStorage.getItem('token') || null)
//
//     const login = async (email, password) => {
//         try {
//             const response = await axios.post('/api/login', { email, password })
//             token.value = response.data.token
//             user.value = response.data.user
//
//             localStorage.setItem('token', token.value)
//             axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
//
//             return true
//         } catch (error) {
//             console.error('Ошибка входа:', error)
//             return false
//         }
//     }
//
//     const register = async (name, email, password) => {
//         try {
//             const response = await axios.post('/api/register', { name, email, password })
//             token.value = response.data.token
//             user.value = response.data.user
//
//             localStorage.setItem('token', token.value)
//             axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
//
//             return true
//         } catch (error) {
//             console.error('Ошибка регистрации:', error)
//             return false
//         }
//     }
//
//     const logout = async () => {
//         try {
//             await axios.post('/api/logout')
//         } catch (error) {
//             console.error('Ошибка выхода:', error)
//         }
//
//         user.value = null
//         token.value = null
//         localStorage.removeItem('token')
//         delete axios.defaults.headers.common['Authorization']
//     }
//
//     const checkAuth = async () => {
//         if (token.value) {
//             try {
//                 axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
//                 const response = await axios.get('/api/me')
//                 user.value = response.data
//             } catch (error) {
//                 logout()
//             }
//         }
//     }
//
//     checkAuth()
//
//     return {
//         user,
//         token,
//         login,
//         register,
//         logout,
//         checkAuth
//     }
// })
import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const token = ref(localStorage.getItem('token') || null)
    const isInitialized = ref(false)

    const login = async (email, password) => {
        try {
            const response = await axios.post('/api/login', { email, password })

            console.log('🔐 Login response:', response.data)

            token.value = response.data.token
            // Убедитесь, что структура правильная
            user.value = response.data.user || response.data.data?.user

            console.log('👤 User after login:', user.value)

            localStorage.setItem('token', token.value)
            axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`

            return { success: true }
        } catch (error) {
            console.error('Ошибка входа:', error)
            return { success: false, error: error.response?.data?.message || 'Ошибка входа' }
        }
    }

    const register = async (name, email, password) => {
        try {
            const response = await axios.post('/api/register', { name, email, password })

            console.log('📝 Register response:', response.data)

            token.value = response.data.token
            user.value = response.data.user || response.data.data?.user

            localStorage.setItem('token', token.value)
            axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`

            return { success: true }
        } catch (error) {
            console.error('Ошибка регистрации:', error)
            return { success: false, error: error.response?.data?.message || 'Ошибка регистрации' }
        }
    }

    const logout = async () => {
        try {
            if (token.value) {
                await axios.post('/api/logout')
            }
        } catch (error) {
            console.error('Ошибка выхода:', error)
        } finally {
            user.value = null
            token.value = null
            isInitialized.value = false
            localStorage.removeItem('token')
            delete axios.defaults.headers.common['Authorization']
        }
    }

    const checkAuth = async () => {
        console.log('🔍 checkAuth started, token:', !!token.value)

        if (token.value) {
            try {
                axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
                const response = await axios.get('/api/user')

                console.log('👤 /api/user response:', response.data)

                // Адаптируйте под структуру вашего API
                if (response.data.user) {
                    user.value = response.data.user
                } else if (response.data.data?.user) {
                    user.value = response.data.data.user
                } else {
                    user.value = response.data
                }

                console.log('✅ User loaded:', {
                    id: user.value?.id,
                    name: user.value?.name,
                    email: user.value?.email,
                    role: user.value?.role
                })

            } catch (error) {
                console.error('Ошибка проверки авторизации:', error)
                user.value = null
                token.value = null
                localStorage.removeItem('token')
                delete axios.defaults.headers.common['Authorization']
            }
        } else {
            console.log('ℹ️ No token found')
        }

        isInitialized.value = true
        return user.value
    }

    // Диагностическая функция для отладки
    const debugUser = () => {
        console.log('🐛 Debug user:', {
            user: user.value,
            hasId: !!user.value?.id,
            hasName: !!user.value?.name,
            hasRole: !!user.value?.role,
            token: !!token.value
        })
        return user.value
    }

    // Автоматическая проверка при создании store
    checkAuth()

    return {
        user,
        token,
        isInitialized,
        login,
        register,
        logout,
        checkAuth,
        debugUser  // для отладки
    }
})
