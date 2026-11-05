/**
 * Конфигурация teo/chat.
 * Плагин перезаписывает значения при app.use(Chat, { ... }).
 */
export const chatConfig = {
    /**
     * Префикс админского API относительно baseURL axios ('/api/').
     * Итоговый путь: /api/{apiPrefix}/... → по умолчанию /api/admin/chat/...
     */
    apiPrefix: 'admin/chat',

    /** Авторизация broadcast-подключений Echo */
    authEndpoint: '/api/broadcasting/auth',

    /**
     * Переопределение параметров Echo/Reverb.
     * По умолчанию берутся VITE_REVERB_* из .env.
     * { key, wsHost, wsPort, wssPort, forceTLS }
     */
    echo: null,
}

/** Склеивает путь внутри админского API: adminUrl('stats') → 'admin/chat/stats' */
export function adminUrl(path = '') {
    const base = String(chatConfig.apiPrefix).replace(/^\/+|\/+$/g, '')
    const suffix = path ? '/' + String(path).replace(/^\/+/g, '') : ''
    return base + suffix
}