<template>
    <header class="site-header">
        <div class="site-header__inner">
            <Link href="/" class="site-header__logo" aria-label="На главную">
                <img
                    v-if="logoSrc && !logoFailed"
                    class="site-header__logo-img"
                    :src="logoSrc"
                    :alt="siteName"
                    @error="logoFailed = true"
                >
                <span v-else class="site-header__logo-text">{{ siteName }}</span>
            </Link>

            <nav v-if="menu.length" class="site-header__nav" aria-label="Основная навигация">
                <ul class="site-header__menu">
                    <li
                        v-for="item in menu"
                        :key="`${item.url}-${item.title}`"
                        class="site-header__menu-item"
                        :class="{ 'has-children': hasChildren(item) }"
                    >
                        <div class="site-header__menu-row">
                            <a
                                v-if="isExternal(item.url)"
                                class="site-header__link"
                                :href="item.url"
                                target="_blank"
                                rel="noopener noreferrer"
                            >{{ item.title }}</a>
                            <Link
                                v-else
                                class="site-header__link"
                                :class="{ 'is-active': isActive(item.url) }"
                                :href="item.url"
                            >{{ item.title }}</Link>

                            <button
                                v-if="hasChildren(item)"
                                type="button"
                                class="site-header__submenu-toggle"
                                :class="{ 'is-open': openSubmenu === item.url }"
                                :aria-expanded="openSubmenu === item.url"
                                :aria-label="`Подменю: ${item.title}`"
                                @click="toggleSubmenu(item.url)"
                            >▾</button>
                        </div>

                        <ul
                            v-if="hasChildren(item)"
                            class="site-header__submenu"
                            :class="{ 'is-open': openSubmenu === item.url }"
                        >
                            <li v-for="child in item.children" :key="`${child.url}-${child.title}`">
                                <a
                                    v-if="isExternal(child.url)"
                                    class="site-header__sublink"
                                    :href="child.url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >{{ child.title }}</a>
                                <Link
                                    v-else
                                    class="site-header__sublink"
                                    :class="{ 'is-active': isActive(child.url) }"
                                    :href="child.url"
                                >{{ child.title }}</Link>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <div class="site-header__contacts">
                <a v-if="phone" class="site-header__phone" :href="phoneHref">{{ phone }}</a>

                <ul v-if="socials.length" class="site-header__socials">
                    <li v-for="social in socials" :key="social.key">
                        <a
                            class="site-social"
                            :href="social.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            :title="social.label"
                            :aria-label="social.label"
                            :style="{ '--social-color': socialColor(social.key) }"
                        >
                            <img
                                v-if="social.logo"
                                class="site-social__logo"
                                :src="social.logo"
                                alt=""
                                loading="lazy"
                            >
                            <svg
                                v-else-if="socialPaths(social.key).length"
                                class="site-social__icon"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    v-for="(path, index) in socialPaths(social.key)"
                                    :key="index"
                                    :d="path"
                                ></path>
                            </svg>
                            <span v-else class="site-social__monogram">{{ socialMonogram(social.key) }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <button
                v-if="menu.length"
                type="button"
                class="site-header__mobile-toggle"
                :class="{ 'is-open': isMobileMenuOpen }"
                :aria-expanded="isMobileMenuOpen"
                :aria-label="isMobileMenuOpen ? 'Закрыть меню' : 'Открыть меню'"
                aria-controls="site-header__mobile-menu"
                @click="toggleMobileMenu"
            >
                <span class="site-header__mobile-toggle-icon" aria-hidden="true"></span>
            </button>
        </div>

        <div
            class="site-header__mobile-backdrop"
            :class="{ 'is-open': isMobileMenuOpen }"
            :aria-hidden="!isMobileMenuOpen"
            @click="closeMobileMenu"
        ></div>

        <div
            id="site-header__mobile-menu"
            class="site-header__mobile-menu"
            :class="{ 'is-open': isMobileMenuOpen }"
            role="navigation"
            aria-label="Мобильное меню"
            :aria-hidden="!isMobileMenuOpen"
        >
            <button
                type="button"
                class="site-header__mobile-close"
                :aria-label="`Закрыть меню`"
                @click="closeMobileMenu"
            >
                <span class="site-header__mobile-close-icon" aria-hidden="true">×</span>
            </button>

            <ul class="site-header__mobile-menu-list">
                <li
                    v-for="item in menu"
                    :key="`${item.url}-${item.title}`"
                    class="site-header__mobile-menu-item"
                    :class="{ 'has-children': hasChildren(item) }"
                >
                    <div class="site-header__mobile-menu-row">
                        <a
                            v-if="isExternal(item.url)"
                            class="site-header__mobile-link"
                            :href="item.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            @click="closeMobileMenu"
                        >{{ item.title }}</a>
                        <Link
                            v-else
                            class="site-header__mobile-link"
                            :class="{ 'is-active': isActive(item.url) }"
                            :href="item.url"
                            @click="closeMobileMenu"
                        >{{ item.title }}</Link>

                        <button
                            v-if="hasChildren(item)"
                            type="button"
                            class="site-header__mobile-submenu-toggle"
                            :class="{ 'is-open': openMobileSubmenu === item.url }"
                            :aria-expanded="openMobileSubmenu === item.url"
                            :aria-label="`Подменю: ${item.title}`"
                            @click="toggleMobileSubmenu(item.url)"
                        >
                            <span class="site-header__mobile-submenu-icon">▾</span>
                        </button>
                    </div>

                    <ul
                        v-if="hasChildren(item)"
                        class="site-header__mobile-submenu"
                        :class="{ 'is-open': openMobileSubmenu === item.url }"
                    >
                        <li v-for="child in item.children" :key="`${child.url}-${child.title}`">
                            <a
                                v-if="isExternal(child.url)"
                                class="site-header__mobile-sublink"
                                :href="child.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                @click="closeMobileMenu"
                            >{{ child.title }}</a>
                            <Link
                                v-else
                                class="site-header__mobile-sublink"
                                :class="{ 'is-active': isActive(child.url) }"
                                :href="child.url"
                                @click="closeMobileMenu"
                            >{{ child.title }}</Link>
                        </li>
                    </ul>
                </li>
            </ul>

            <div v-if="phone" class="site-header__mobile-contacts">
                <a v-if="phoneHref" :href="phoneHref" class="site-header__mobile-phone">{{ phone }}</a>
            </div>

            <ul v-if="socials.length" class="site-header__mobile-socials">
                <li v-for="social in socials" :key="social.key">
                    <a
                        class="site-social"
                        :href="social.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        :title="social.label"
                        :aria-label="social.label"
                        :style="{ '--social-color': socialColor(social.key) }"
                    >
                        <img
                            v-if="social.logo"
                            class="site-social__logo"
                            :src="social.logo"
                            alt=""
                            loading="lazy"
                        >
                        <svg
                            v-else-if="socialPaths(social.key).length"
                            class="site-social__icon"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                v-for="(path, index) in socialPaths(social.key)"
                                :key="index"
                                :d="path"
                            ></path>
                        </svg>
                        <span v-else class="site-social__monogram">{{ socialMonogram(social.key) }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </header>
</template>

<script setup>
/**
 * Публичная шапка сайта: лого слева, меню по центру, телефон и соцсети справа.
 *
 * Данные — из общего пропса Inertia `settings` (SeoBuilder::siteSettings):
 * name, logo, phone, menu_items, socials.
 */
import { computed, ref, watch, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { socialIcon, socialMonogram } from './socialIcons.js'

const page = usePage()
const logoFailed = ref(false)

// Шапка монтируется раньше Inertia-страницы, поэтому page.props может быть
// ещё не инициализирован — читаем безопасно и обновляемся реактивно.
const settings = computed(() => page?.props?.settings ?? {})

const siteName = computed(() => String(settings.value.name || 'Мой сайт'))

const logoSrc = computed(() => {
    const logo = settings.value.logo
    return typeof logo === 'string' ? logo.trim() : ''
})

const phone = computed(() => String(settings.value.phone ?? '').trim())

const phoneHref = computed(() => {
    if (!phone.value) return ''

    const digits = phone.value.replace(/[^\d]/g, '')
    if (!digits) return ''

    return `tel:${phone.value.startsWith('+') ? '+' : ''}${digits}`
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

const currentPath = computed(() => String(page?.url || '/').split(/[?#]/)[0] || '/')

// Раскрытое подменю (клик/тап; на десктопе также работает hover)
const openSubmenu = ref('')

const hasChildren = (item) => Array.isArray(item?.children) && item.children.length > 0

const toggleSubmenu = (url) => {
    openSubmenu.value = openSubmenu.value === url ? '' : url
}

watch(currentPath, () => {
    openSubmenu.value = ''
})

// Мобильное меню (гамбургер, выезжает справа)
const isMobileMenuOpen = ref(false)
const openMobileSubmenu = ref('')

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value
    document.body.classList.toggle('site-menu-locked', isMobileMenuOpen.value)
}

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false
    openMobileSubmenu.value = ''
    document.body.classList.remove('site-menu-locked')
}

const toggleMobileSubmenu = (url) => {
    openMobileSubmenu.value = openMobileSubmenu.value === url ? '' : url
}

const handleResize = () => {
    if (window.innerWidth > 900 && isMobileMenuOpen.value) {
        closeMobileMenu()
    }
}

onMounted(() => {
    window.addEventListener('resize', handleResize)
})
onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
})

const isExternal = (url) => /^https?:\/\//i.test(String(url))

/**
 * Активный пункт: для «/» — точное совпадение, для остальных — сам путь
 * или вложенная страница (/portfolio активен на /portfolio/object).
 */
const isActive = (url) => {
    if (isExternal(url)) return false

    const target = String(url || '/')
    if (target === '/') return currentPath.value === '/'

    const normalized = target.replace(/\/+$/, '')
    return currentPath.value === normalized || currentPath.value.startsWith(`${normalized}/`)
}

const socialPaths = (key) => socialIcon(key).paths

const socialColor = (key) => socialIcon(key).color
</script>

<style scoped>
.site-header {
    position: sticky;
    top: 0;
    z-index: 80;
    border-bottom: 1px solid #e5e7eb;
    background: #fff;
}

.site-header__inner {
    display: grid;
    grid-template-columns:  auto auto minmax(0, 1fr);
    gap: 24px;
    align-items: center;
    max-width: 1120px;
    margin: 0 auto;
    padding: 14px 20px;
}

.site-header__logo {
    display: inline-flex;
    align-items: center;
    justify-self: start;
    min-width: 0;
    color: #111827;
    text-decoration: none;

}

.site-header__logo-img {
    display: block;
    width: auto;
    max-width: 220px;
    max-height: 44px;
}

.site-header__logo-text {
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.site-header__nav {
    justify-self: center;
    min-width: 0;
}

.site-header__menu {
    display: flex;
    gap: 26px;
    align-items: center;
    margin: 0;
    padding: 0;
    list-style: none;
}

.site-header__link {
    display: inline-block;
    padding: 4px 0;
    color: #374151;
    font-size: 0.95rem;
    font-weight: 500;
    line-height: 1.4;
    text-decoration: none;
    white-space: nowrap;
    border-bottom: 2px solid transparent;
    transition: color 0.15s ease, border-color 0.15s ease;
}

.site-header__link:hover {
    color: #2563eb;
}

.site-header__link.is-active {
    color: #2563eb;
    border-bottom-color: #2563eb;
}

.site-header__menu-item {
    position: relative;
}

.site-header__menu-row {
    display: flex;
    gap: 2px;
    align-items: center;
}

.site-header__submenu-toggle {
    padding: 2px 4px;
    border: 0;
    background: none;
    color: #6b7280;
    font-size: 0.7rem;
    line-height: 1;
    cursor: pointer;
    transition: color 0.15s ease, transform 0.15s ease;
}

.site-header__submenu-toggle:hover,
.site-header__submenu-toggle.is-open {
    color: #2563eb;
}

.site-header__submenu-toggle.is-open {
    transform: rotate(180deg);
}

.site-header__submenu {
    position: absolute;
    top: calc(100% + 10px);
    left: 0;
    z-index: 95;
    display: block;
    min-width: 260px;
    max-width: 340px;
    margin: 0;
    padding: 8px;
    list-style: none;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.12);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-4px);
    transition: opacity 0.15s ease, transform 0.15s ease, visibility 0.15s ease;
}

.site-header__menu-item:hover > .site-header__submenu,
.site-header__menu-item:focus-within > .site-header__submenu,
.site-header__submenu.is-open {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.site-header__sublink {
    display: block;
    padding: 8px 10px;
    border-radius: 8px;
    color: #374151;
    font-size: 0.9rem;
    line-height: 1.4;
    text-decoration: none;
    white-space: normal;
    transition: background 0.15s ease, color 0.15s ease;
}

.site-header__sublink:hover,
.site-header__sublink.is-active {
    background: #eff6ff;
    color: #2563eb;
}

.site-header__contacts {
    display: flex;
    gap: 14px;
    align-items: center;
    justify-self: end;
    min-width: 0;
}

.site-header__phone {
    color: #111827;
    font-size: 0.95rem;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: color 0.15s ease;
}

.site-header__phone:hover {
    color: #2563eb;
}

.site-header__socials {
    display: flex;
    gap: 8px;
    align-items: center;
    margin: 0;
    padding: 0;
    list-style: none;
}

.site-social {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border: 1px solid #e5e7eb;
    border-radius: 50%;
    background: #f9fafb;
    color: #6b7280;
    text-decoration: none;
    transition: color 0.15s ease, border-color 0.15s ease, background 0.15s ease;
}

.site-social:hover,
.site-social:focus-visible {
    border-color: var(--social-color, #2563eb);
    background: #fff;
    color: var(--social-color, #2563eb);
    outline: none;
}

.site-social__icon {
    width: 17px;
    height: 17px;
    fill: currentColor;
}

.site-social__logo {
    display: block;
    width: 18px;
    height: 18px;
    object-fit: contain;
}

.site-social__monogram {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

@media (max-width: 900px) {
    .site-header__inner {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 10px 16px;
        padding: 12px 20px;
    }

    .site-header__logo {
        order: 1;
    }

    .site-header__contacts {
        order: 2;
    }

    .site-header__nav {
        order: 3;
        width: 100%;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .site-header__nav::-webkit-scrollbar {
        display: none;
    }

    .site-header__menu {
        justify-content: center;
        gap: 18px;
        padding-bottom: 2px;
    }

    .site-header__submenu {
        position: static;
        display: none;
        min-width: 0;
        max-width: none;
        padding: 4px 0 4px 12px;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
        opacity: 1;
        visibility: visible;
        transform: none;
    }

    .site-header__submenu.is-open {
        display: block;
    }

    .site-header__menu-item:hover > .site-header__submenu {
        display: none;
    }

    .site-header__menu-item:hover > .site-header__submenu.is-open {
        display: block;
    }

    .site-header__sublink {
        padding: 6px 8px;
    }
}

@media (max-width: 560px) {
    .site-header__inner {
        padding: 10px 16px;
    }

    .site-header__logo-img {
        max-width: 150px;
        max-height: 36px;
    }

    .site-header__logo-text {
        font-size: 1.05rem;
    }

    .site-header__contacts {
        gap: 10px;
    }

    .site-header__phone {
        font-size: 0.85rem;
    }

    .site-social {
        width: 30px;
        height: 30px;
    }

    .site-social__icon {
        width: 15px;
        height: 15px;
    }

    .site-header__link {
        font-size: 0.9rem;
    }
}

/* ---------- Мобильное меню (гамбургер, выезжает справа) ---------- */
.site-header__mobile-toggle {
    display: none;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    padding: 0;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: #fff;
    color: #111827;
    cursor: pointer;
    transition: border-color 0.15s ease, color 0.15s ease;
}

.site-header__mobile-toggle:hover {
    border-color: #2563eb;
    color: #2563eb;
}

.site-header__mobile-toggle-icon {
    display: inline-block;
    width: 20px;
    height: 2px;
    background: currentColor;
    border-radius: 2px;
    position: relative;
}

.site-header__mobile-toggle-icon::before,
.site-header__mobile-toggle-icon::after {
    content: '';
    position: absolute;
    left: 0;
    width: 100%;
    height: 2px;
    background: currentColor;
    border-radius: 2px;
    transition: transform 0.2s ease, top 0.2s ease, bottom 0.2s ease;
}

.site-header__mobile-toggle-icon::before { top: -6px; }
.site-header__mobile-toggle-icon::after { bottom: -6px; }

.site-header__mobile-toggle.is-open .site-header__mobile-toggle-icon {
    background: transparent;
}
.site-header__mobile-toggle.is-open .site-header__mobile-toggle-icon::before {
    top: 0;
    transform: rotate(45deg);
}
.site-header__mobile-toggle.is-open .site-header__mobile-toggle-icon::after {
    bottom: 0;
    transform: rotate(-45deg);
}

/* Бэкдроп */
.site-header__mobile-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.3s ease, visibility 0.3s ease;
    z-index: 85;
}

.site-header__mobile-backdrop.is-open {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}

/* Панель меню, выезжающая справа */
.site-header__mobile-menu {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    max-width: 320px;
    background: #fff;
    transform: translateX(100%);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                opacity 0.3s ease,
                visibility 0.3s ease;
    z-index: 90;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    padding: 20px;
    box-shadow: -8px 0 24px rgba(0, 0, 0, 0.08);
}

.site-header__mobile-menu.is-open {
    transform: translateX(0);
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}

.site-header__mobile-close {
    position: absolute;
    top: 14px;
    right: 14px;
    background: transparent;
    border: none;
    cursor: pointer;
    color: #111827;
    padding: 6px;
    border-radius: 6px;
    line-height: 1;
    transition: background 0.15s ease, color 0.15s ease;
}

.site-header__mobile-close:hover {
    background: #f3f4f6;
    color: #2563eb;
}

.site-header__mobile-close-icon {
    display: inline-block;
    font-size: 26px;
    font-weight: 700;
    line-height: 1;
}

/* Список ссылок */
.site-header__mobile-menu-list {
    margin: 0;
    padding: 0;
    list-style: none;
    flex: 1;
}

.site-header__mobile-menu-item {
    border-bottom: 1px solid #f3f4f6;
}

.site-header__mobile-menu-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 0;
}

.site-header__mobile-link {
    color: #111827;
    text-decoration: none;
    font-size: 1.05rem;
    font-weight: 600;
    white-space: nowrap;
    transition: color 0.15s ease;
}

.site-header__mobile-link:hover,
.site-header__mobile-link.is-active {
    color: #2563eb;
}

.site-header__mobile-submenu-toggle {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 6px;
    font-size: 1rem;
    line-height: 1;
    flex-shrink: 0;
    transition: color 0.15s ease;
}

.site-header__mobile-submenu-toggle:hover {
    color: #2563eb;
}

.site-header__mobile-submenu {
    display: none;
    padding-left: 22px;
    margin: 6px 0 0 0;
    list-style: none;
}

.site-header__mobile-submenu.is-open {
    display: block;
}

.site-header__mobile-sublink {
    display: block;
    padding: 9px 0;
    color: #374151;
    font-size: 0.95rem;
    text-decoration: none;
    transition: color 0.15s ease;
}

.site-header__mobile-sublink:hover,
.site-header__mobile-sublink.is-active {
    color: #2563eb;
}

/* Телефон и соцсети в панели */
.site-header__mobile-contacts {
    margin-top: 32px;
}

.site-header__mobile-phone {
    color: #111827;
    font-size: 1rem;
    font-weight: 700;
    text-decoration: none;
    transition: color 0.15s ease;
}

.site-header__mobile-phone:hover {
    color: #2563eb;
}

.site-header__mobile-socials {
    display: flex;
    gap: 10px;
    margin-top: 16px;
    padding: 0;
    list-style: none;
}

/* ---------- Адаптив: гамбургер только на мобильных ---------- */
@media (max-width: 900px) {
    .site-header__mobile-toggle {
        display: inline-flex;
    }

    .site-header__nav {
        display: none;
    }

    .site-header__contacts {
        display: none;
    }
}

@media (min-width: 901px) {
    .site-header__mobile-toggle,
    .site-header__mobile-backdrop,
    .site-header__mobile-menu {
        display: none !important;
    }
}

/* Блокировка скролла body при открытом меню */
:deep(body.site-menu-locked) {
    overflow: hidden;
    touch-action: none;
}
</style>
