<template>
    <footer class="site-footer">
        <div class="site-footer__inner">
            <div class="site-footer__brand">
                <Link href="/" class="site-footer__logo" aria-label="На главную">
                    <img
                        v-if="logoSrc && !logoFailed"
                        class="site-footer__logo-img"
                        :src="logoSrc"
                        :alt="siteName"
                        @error="logoFailed = true"
                    >
                    <span v-else class="site-footer__logo-text">{{ siteName }}</span>
                </Link>

                <p v-if="description" class="site-footer__about">{{ description }}</p>

                <ul v-if="socials.length" class="site-footer__socials">
                    <li v-for="social in socials" :key="social.key">
                        <a
                            class="site-footer__social"
                            :href="social.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            :title="social.label"
                            :aria-label="social.label"
                            :style="{ '--social-color': socialColor(social.key) }"
                        >
                            <img
                                v-if="social.logo"
                                class="site-footer__social-logo"
                                :src="social.logo"
                                alt=""
                                loading="lazy"
                            >
                            <svg
                                v-else-if="socialPaths(social.key).length"
                                class="site-footer__social-icon"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    v-for="(path, index) in socialPaths(social.key)"
                                    :key="index"
                                    :d="path"
                                ></path>
                            </svg>
                            <span v-else class="site-footer__social-monogram">{{ socialMonogram(social.key) }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <nav v-if="menu.length" class="site-footer__nav" aria-label="Навигация в футере">
                <h2 class="site-footer__title">Навигация</h2>
                <ul class="site-footer__links">
                    <li v-for="item in menu" :key="`${item.url}-${item.title}`">
                        <a
                            v-if="isExternal(item.url)"
                            :href="item.url"
                            target="_blank"
                            rel="noopener noreferrer"
                        >{{ item.title }}</a>
                        <Link v-else :href="item.url">{{ item.title }}</Link>

                        <ul v-if="hasChildren(item)" class="site-footer__sublinks">
                            <li v-for="child in item.children" :key="`${child.url}-${child.title}`">
                                <a
                                    v-if="isExternal(child.url)"
                                    :href="child.url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >{{ child.title }}</a>
                                <Link v-else :href="child.url">{{ child.title }}</Link>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <div class="site-footer__contacts">
                <h2 class="site-footer__title">Контакты</h2>

                <p v-if="address">{{ address }}</p>
                <p v-if="phone"><a :href="phoneHref">{{ phone }}</a></p>
                <p v-if="email"><a :href="emailHref">{{ email }}</a></p>
                <p v-if="!address && !phone && !email" class="site-footer__muted">
                    Контактные данные заполняются в админке.
                </p>
            </div>
        </div>

        <div class="site-footer__bottom">
            <div class="site-footer__bottom-inner">
                © {{ year }} {{ siteName }}. Все права защищены.
            </div>
        </div>
    </footer>
</template>

<script setup>
/**
 * Футер сайта: бренд, реальные страницы (с подпунктами), контакты и соцсети.
 *
 * Данные — из общего пропса Inertia `settings` (SeoBuilder::siteSettings).
 */
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { socialIcon, socialMonogram } from './socialIcons.js'

const page = usePage()
const logoFailed = ref(false)

const settings = computed(() => page?.props?.settings ?? {})

const siteName = computed(() => String(settings.value.name || 'Мой сайт'))

const logoSrc = computed(() => {
    const logo = settings.value.logo
    return typeof logo === 'string' ? logo.trim() : ''
})

const description = computed(() => String(settings.value.description ?? '').trim())

const address = computed(() => String(settings.value.address ?? '').trim())
const phone = computed(() => String(settings.value.phone ?? '').trim())
const email = computed(() => String(settings.value.email ?? '').trim())

const phoneHref = computed(() => {
    if (!phone.value) return ''

    const digits = phone.value.replace(/[^\d]/g, '')
    if (!digits) return ''

    return `tel:${phone.value.startsWith('+') ? '+' : ''}${digits}`
})

const emailHref = computed(() => {
    if (!email.value) return ''
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value) ? `mailto:${email.value}` : ''
})

const socials = computed(() => {
    const items = settings.value.socials
    if (!Array.isArray(items)) return []

    return items.filter((item) => item && item.key && item.url)
})

const menu = computed(() => {
    const items = settings.value.menu_items
    if (!Array.isArray(items)) return []

    return items.filter((item) => item && item.title && item.url)
})

const year = new Date().getFullYear()

const isExternal = (url) => /^https?:\/\//i.test(String(url))

const hasChildren = (item) => Array.isArray(item?.children) && item.children.length > 0

const socialPaths = (key) => socialIcon(key).paths
const socialColor = (key) => socialIcon(key).color
</script>

<style scoped>
.site-footer {
    margin-top: 48px;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    color: #374151;
}

.site-footer__inner {
    display: grid;
    grid-template-columns: minmax(220px, 1.4fr) minmax(180px, 1fr) minmax(200px, 1fr);
    gap: 32px;
    max-width: 1120px;
    margin: 0 auto;
    padding: 40px 20px 28px;
}

.site-footer__logo {
    display: inline-flex;
    align-items: center;
    color: #111827;
    text-decoration: none;
}

.site-footer__logo-img {
    display: block;
    width: auto;
    max-width: 200px;
    max-height: 40px;
}

.site-footer__logo-text {
    font-size: 1.2rem;
    font-weight: 800;
    letter-spacing: -0.01em;
}

.site-footer__about {
    margin: 12px 0 0;
    color: #6b7280;
    font-size: 0.92rem;
    line-height: 1.6;
}

.site-footer__title {
    margin: 0 0 12px;
    color: #111827;
    font-size: 1rem;
    font-weight: 700;
}

.site-footer__links,
.site-footer__sublinks {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin: 0;
    padding: 0;
    list-style: none;
    font-size: 0.92rem;
}

.site-footer__sublinks {
    gap: 6px;
    margin-top: 6px;
    padding-left: 12px;
    color: #6b7280;
    font-size: 0.86rem;
}

.site-footer__links a,
.site-footer__sublinks a {
    color: #374151;
    text-decoration: none;
    line-height: 1.4;
    transition: color 0.15s ease;
}

.site-footer__links a:hover,
.site-footer__sublinks a:hover {
    color: #2563eb;
}

.site-footer__contacts p {
    margin: 0 0 8px;
    font-size: 0.92rem;
    line-height: 1.5;
}

.site-footer__contacts a {
    color: #374151;
    text-decoration: none;
    transition: color 0.15s ease;
}

.site-footer__contacts a:hover {
    color: #2563eb;
}

.site-footer__muted {
    color: #9ca3af;
}

.site-footer__socials {
    display: flex;
    gap: 8px;
    align-items: center;
    margin: 16px 0 0;
    padding: 0;
    list-style: none;
}

.site-footer__social {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border: 1px solid #e5e7eb;
    border-radius: 50%;
    background: #fff;
    color: #6b7280;
    text-decoration: none;
    transition: color 0.15s ease, border-color 0.15s ease;
}

.site-footer__social:hover,
.site-footer__social:focus-visible {
    border-color: var(--social-color, #2563eb);
    color: var(--social-color, #2563eb);
    outline: none;
}

.site-footer__social-icon {
    width: 17px;
    height: 17px;
    fill: currentColor;
}

.site-footer__social-logo {
    display: block;
    width: 18px;
    height: 18px;
    object-fit: contain;
}

.site-footer__social-monogram {
    font-size: 0.7rem;
    font-weight: 700;
}

.site-footer__bottom {
    border-top: 1px solid #e5e7eb;
}

.site-footer__bottom-inner {
    max-width: 1120px;
    margin: 0 auto;
    padding: 16px 20px 24px;
    color: #6b7280;
    font-size: 0.85rem;
}

@media (max-width: 900px) {
    .site-footer__inner {
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .site-footer__brand {
        grid-column: 1 / -1;
    }
}

@media (max-width: 560px) {
    .site-footer__inner {
        grid-template-columns: 1fr;
        padding: 28px 16px 20px;
    }

    .site-footer__bottom-inner {
        padding: 14px 16px 20px;
    }
}
</style>
