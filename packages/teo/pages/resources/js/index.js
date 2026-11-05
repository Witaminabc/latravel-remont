import { getCurrentScope, onScopeDispose, unref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * SEO-синхронизация для клиентских навигаций Inertia.
 *
 * Первичный HTML уже содержит мету (серверный рендер в resources/views/inertia.blade.php),
 * здесь она обновляется при переходах между страницами.
 */

const META_KEYS = [
    ['name', 'description', 'description'],
    ['name', 'keywords', 'keywords'],
    ['name', 'robots', 'robots'],
    ['property', 'og:type', 'og_type'],
    ['property', 'og:site_name', 'og_site_name'],
    ['property', 'og:locale', 'og_locale'],
    ['property', 'og:title', 'og_title'],
    ['property', 'og:description', 'og_description'],
    ['property', 'og:url', 'og_url'],
    ['property', 'og:image', 'og_image'],
    ['name', 'twitter:card', 'twitter_card'],
    ['name', 'twitter:title', 'twitter_title'],
    ['name', 'twitter:description', 'twitter_description'],
    ['name', 'twitter:image', 'twitter_image'],
]

const applyMeta = (attr, key, value) => {
    const selector = `meta[${attr}="${key}"]`
    const existing = document.head.querySelector(selector)

    if (value === null || value === undefined || value === '') {
        existing?.remove()
        return
    }

    if (existing) {
        existing.setAttribute('content', value)
        return
    }

    const el = document.createElement('meta')
    el.setAttribute(attr, key)
    el.setAttribute('content', value)
    document.head.appendChild(el)
}

const applyCanonical = (value) => {
    const existing = document.head.querySelector('link[rel="canonical"]')

    if (!value) {
        return
    }

    if (existing) {
        existing.setAttribute('href', value)
        return
    }

    const el = document.createElement('link')
    el.setAttribute('rel', 'canonical')
    el.setAttribute('href', value)
    document.head.appendChild(el)
}

/**
 * Применить объект seo (props.seo из сервера) к <head> документа.
 * Обновляются только ключи, присутствующие в объекте.
 */
export function applySeo (seo) {
    if (!seo || typeof seo !== 'object') {
        return
    }

    if (typeof seo.title === 'string') {
        document.title = seo.title
    }

    for (const [attr, key, prop] of META_KEYS) {
        if (Object.prototype.hasOwnProperty.call(seo, prop)) {
            applyMeta(attr, key, seo[prop])
        }
    }

    if (Object.prototype.hasOwnProperty.call(seo, 'canonical')) {
        applyCanonical(seo.canonical)
    }

    if (Object.prototype.hasOwnProperty.call(seo, 'json_ld')) {
        applyJsonLd(seo.json_ld)
    }
}

/** Обновление структурированных данных (seo.json_ld) при клиентских навигациях. */
const applyJsonLd = (schemas) => {
    document.head
        .querySelectorAll('script[type="application/ld+json"][data-teo-jsonld]')
        .forEach((el) => el.remove())

    if (!Array.isArray(schemas)) {
        return
    }

    for (const schema of schemas) {
        if (!schema || typeof schema !== 'object') {
            continue
        }

        const el = document.createElement('script')
        el.type = 'application/ld+json'
        el.dataset.teoJsonld = ''
        el.textContent = JSON.stringify(schema)
        document.head.appendChild(el)
    }
}

let syncInstalled = false

/**
 * Подписка на успешные Inertia-визиты: обновляет мету из props.seo.
 * Идемпотентен — можно вызывать только один раз при старте приложения.
 */
export function installSeoSync () {
    if (syncInstalled) {
        return
    }

    syncInstalled = true

    router.on('success', (event) => {
        const page = event?.detail?.page
        if (page?.props?.seo) {
            applySeo(page.props.seo)
        }
    })
}

/**
 * Композабель: реактивно применяет seo из reactive/computed/геттера.
 *
 * useSeo(() => page.value.seo)
 * useSeo(computed(() => props.seo))
 */
export function useSeo (source) {
    const read = () => (typeof source === 'function' ? source() : unref(source))

    const stop = watch(read, (value) => applySeo(value), { immediate: true, deep: true })

    if (getCurrentScope()) {
        onScopeDispose(stop)
    }

    return { stop }
}

export default { applySeo, installSeoSync, useSeo }