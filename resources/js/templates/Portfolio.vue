<template>
    <div class="page page--portfolio">
        <section class="portfolio-hero">
            <h1>{{ acf.portfolio_heading || page.title }}</h1>
            <p v-if="acf.portfolio_intro" class="portfolio-hero__intro">{{ acf.portfolio_intro }}</p>
        </section>

        <section class="portfolio-grid">
            <article v-for="(item, index) in items" :key="index" class="portfolio-card">
                <PortfolioImageSlider
                    :images="cardImages(item)"
                    :title="item.title"
                    variant="compact"
                />
                <div class="portfolio-card__body">
                    <h2 class="portfolio-card__title">{{ item.title }}</h2>
                    <p v-if="item.address" class="portfolio-card__meta">{{ item.address }}</p>
                    <ul class="portfolio-card__info">
                        <li v-if="item.area">Площадь: {{ item.area }} м²</li>
                        <li v-if="item.term">Сроки: {{ item.term }} дней</li>
                        <li v-if="item.price_works">Работы: {{ formatPrice(item.price_works) }} руб.</li>
                        <li v-if="item.price_materials">Материалы: {{ formatPrice(item.price_materials) }} руб.</li>
                    </ul>
                    <a v-if="cardLink(item)" class="portfolio-card__link" :href="cardLink(item)">Посмотреть объект</a>
                </div>
            </article>
        </section>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import PortfolioImageSlider from './blocks/PortfolioImageSlider.vue'

const props = defineProps({
    page: { type: Object, required: true },
    acf: { type: Object, default: () => ({}) },
    seo: { type: Object, default: () => ({}) },
    settings: { type: Object, default: () => ({}) },
    template: { type: String, default: '' },
})

const FORMATTER = new Intl.NumberFormat('ru-RU')
const formatPrice = (value) => value !== null && value !== undefined ? FORMATTER.format(value) : '—'

const FALLBACK_ITEMS = [
    {
        title: 'Ремонт двухкомнатной квартиры в ЖК «Сиберово»',
        address: 'ул. Владимира Шнеппа, дом 5',
        area: 63,
        term: 71,
        price_works: 986000,
        price_materials: 350000,
        image: '/storage/uploads/portfolio/gotovyj-remont-2k-kvartiry-zhk-siberovo-kazan-17-800x600.jpg',
        link_url: '/portfolio/remont-siberovo',
        gallery: [
            '/storage/uploads/portfolio/gotovyj-remont-2k-kvartiry-zhk-siberovo-kazan-17-800x600.jpg',
            '/storage/uploads/portfolio/gotovyj-remont-dvuhkomnatnoj-kvartiry-zhk-merkurij-1-800x600.jpg',
            '/storage/uploads/portfolio/фото-1-800x600.jpg',
            '/storage/uploads/portfolio/DSC01695-1-800x600.jpg',
            '/storage/uploads/portfolio/Ванная-1-800x600.jpg',
        ],
    },
    {
        title: 'Ремонт двухкомнатной квартиры в ЖК «Меркурий»',
        address: 'ул. Серова 41а',
        area: 57,
        term: 105,
        price_works: 931000,
        price_materials: 297000,
        image: '/storage/uploads/portfolio/gotovyj-remont-dvuhkomnatnoj-kvartiry-zhk-merkurij-1-800x600.jpg',
        link_url: '/portfolio/remont-merkurij',
        gallery: [
            '/storage/uploads/portfolio/gotovyj-remont-dvuhkomnatnoj-kvartiry-zhk-merkurij-1-800x600.jpg',
            '/storage/uploads/portfolio/gotovyj-remont-2k-kvartiry-zhk-siberovo-kazan-17-800x600.jpg',
            '/storage/uploads/portfolio/DSC01695-1-800x600.jpg',
            '/storage/uploads/portfolio/фото-1-800x600.jpg',
            '/storage/uploads/portfolio/Ремонт-ванны-1-800x600.jpg',
        ],
    },
    {
        title: 'Ремонт однокомнатной квартиры в ЖК «Мой Ритм»',
        address: 'ул. Назиба Жиганова 2а',
        area: 37,
        term: 75,
        price_works: 735000,
        price_materials: 245000,
        image: '/storage/uploads/portfolio/фото-1-800x600.jpg',
        link_url: '/portfolio/remont-moy-ritm',
        gallery: [
            '/storage/uploads/portfolio/фото-1-800x600.jpg',
            '/storage/uploads/portfolio/Ванная-1-800x600.jpg',
            '/storage/uploads/portfolio/Ремонт-ванны-1-800x600.jpg',
            '/storage/uploads/portfolio/gotovyj-remont-2k-kvartiry-zhk-siberovo-kazan-17-800x600.jpg',
            '/storage/uploads/portfolio/gotovyj-remont-dvuhkomnatnoj-kvartiry-zhk-merkurij-1-800x600.jpg',
        ],
    },
    {
        title: 'Ремонт квартиры студии в ЖК «Открытие»',
        address: 'ул. Аделя Кутуя, 100',
        area: 28,
        term: 20,
        price_works: 420000,
        price_materials: 116000,
        image: '/storage/uploads/portfolio/DSC01695-1-800x600.jpg',
        link_url: '/portfolio/remont-studiya',
        gallery: [
            '/storage/uploads/portfolio/DSC01695-1-800x600.jpg',
            '/storage/uploads/portfolio/gotovyj-remont-dvuhkomnatnoj-kvartiry-zhk-merkurij-1-800x600.jpg',
            '/storage/uploads/portfolio/Ремонт-ванны-1-800x600.jpg',
            '/storage/uploads/portfolio/gotovyj-remont-2k-kvartiry-zhk-siberovo-kazan-17-800x600.jpg',
            '/storage/uploads/portfolio/фото-1-800x600.jpg',
        ],
    },
    {
        title: 'Ремонт ванной комнаты под ключ',
        address: 'г. Казань',
        area: 5.7,
        term: 19,
        price_works: 235000,
        price_materials: 90000,
        image: '/storage/uploads/portfolio/Ванная-1-800x600.jpg',
        link_url: '/portfolio/remont-vannaya',
        gallery: [
            '/storage/uploads/portfolio/Ванная-1-800x600.jpg',
            '/storage/uploads/portfolio/Ремонт-ванны-1-800x600.jpg',
            '/storage/uploads/portfolio/фото-1-800x600.jpg',
            '/storage/uploads/portfolio/DSC01695-1-800x600.jpg',
            '/storage/uploads/portfolio/gotovyj-remont-2k-kvartiry-zhk-siberovo-kazan-17-800x600.jpg',
        ],
    },
    {
        title: 'Ремонт санузла под ключ',
        address: 'г. Казань',
        area: 3.8,
        term: 16,
        price_works: 115000,
        price_materials: 43000,
        image: '/storage/uploads/portfolio/Ремонт-ванны-1-800x600.jpg',
        link_url: '/portfolio/remont-sanzuzel',
        gallery: [
            '/storage/uploads/portfolio/Ремонт-ванны-1-800x600.jpg',
            '/storage/uploads/portfolio/Ванная-1-800x600.jpg',
            '/storage/uploads/portfolio/DSC01695-1-800x600.jpg',
            '/storage/uploads/portfolio/фото-1-800x600.jpg',
            '/storage/uploads/portfolio/gotovyj-remont-dvuhkomnatnoj-kvartiry-zhk-merkurij-1-800x600.jpg',
        ],
    },
]

const items = computed(() => {
    const arr = props.acf?.portfolio_items
    return Array.isArray(arr) && arr.length ? arr : FALLBACK_ITEMS
})

const toUrl = (value) => {
    if (typeof value === 'string') return value
    if (value && typeof value === 'object') return value.image || value.url || ''
    return ''
}

/**
 * Фотографии для слайдера карточки: главное изображение + галерея,
 * без дублей. Значения ACF могут приходить строками или объектами.
 */
const cardImages = (item) => {
    const urls = []
    const push = (value) => {
        const src = toUrl(value)
        if (src && !urls.includes(src)) urls.push(src)
    }
    push(item?.image)
    if (Array.isArray(item?.gallery)) item.gallery.forEach(push)
    return urls
}

/**
 * Ссылка на детальную страницу объекта.
 * ACF-поле link_url имеет приоритет, иначе собираем из slug
 * (страницы вложены в /portfolio).
 */
const cardLink = (item) => {
    if (item?.link_url) return item.link_url
    if (item?.slug) return `/portfolio/${item.slug}`
    return ''
}
</script>

<style scoped>
.page--portfolio { max-width: 1120px; margin: 0 auto; padding: 56px 20px 80px; }
.portfolio-hero { margin-bottom: 32px; }
.portfolio-hero h1 { margin: 0 0 8px; font-size: 2rem; color: #111827; }
.portfolio-hero__intro { color: #4b5563; font-size: 1.1rem; }
.portfolio-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; }
.portfolio-card { border: 1px solid #e5e7eb; border-radius: 20px; overflow: hidden; background: #fff; display: flex; flex-direction: column; }
.portfolio-card__body { padding: 24px; flex: 1; display: flex; flex-direction: column; }
.portfolio-card__title { margin: 0 0 8px; font-size: 1.1rem; line-height: 1.3; }
.portfolio-card__meta { margin: 0 0 8px; color: #6b7280; font-size: 0.9rem; }
.portfolio-card__info { margin: 0 0 16px; padding: 0; list-style: none; color: #374151; font-size: 0.9rem; flex: 1; }
.portfolio-card__info li { margin-bottom: 4px; }
.portfolio-card__link { display: inline-block; padding: 12px 22px; background: #2563eb; color: #fff; border-radius: 12px; text-decoration: none; font-weight: 600; text-align: center; }
.portfolio-card__link:hover { background: #1d4ed8; }
</style>
