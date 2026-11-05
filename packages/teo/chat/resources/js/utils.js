/** Инициалы для аватара: "Иван Петров" → "ИП" */
export function initials(name = '') {
    const parts = String(name).trim().split(/\s+/).filter(Boolean)
    if (!parts.length) return '?'
    return parts.slice(0, 2).map(p => p[0].toUpperCase()).join('')
}

/** Время для списка диалогов: сегодня — HH:MM, вчера — «вчера», иначе — дата */
export function formatThreadTime(iso) {
    if (!iso) return ''
    const d = new Date(iso)
    const now = new Date()
    const yesterday = new Date(now)
    yesterday.setDate(now.getDate() - 1)

    if (d.toDateString() === now.toDateString()) {
        return d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
    }
    if (d.toDateString() === yesterday.toDateString()) {
        return 'вчера'
    }
    return d.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit' })
}

/** Время сообщения: HH:MM */
export function formatMessageTime(iso) {
    if (!iso) return ''
    return new Date(iso).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
}

/** Ключ дня для группировки: YYYY-M-D */
export function dayKey(iso) {
    const d = new Date(iso)
    return `${d.getFullYear()}-${d.getMonth()}-${d.getDate()}`
}

/** Заголовок дня: «Сегодня», «Вчера» или полная дата */
export function formatDayLabel(iso) {
    const d = new Date(iso)
    const now = new Date()
    const yesterday = new Date(now)
    yesterday.setDate(now.getDate() - 1)

    if (d.toDateString() === now.toDateString()) return 'Сегодня'
    if (d.toDateString() === yesterday.toDateString()) return 'Вчера'
    return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}