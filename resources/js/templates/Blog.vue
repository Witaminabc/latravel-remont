<template>
    <div class="page page--blog">
        <section class="blog-hero">
            <h1>{{ acf.blog_heading || page.title }}</h1>
            <p v-if="acf.blog_intro" class="blog-hero__intro">{{ acf.blog_intro }}</p>
        </section>

        <section class="blog-grid">
            <article
                v-for="(item, index) in items"
                :key="`blog-${index}`"
                class="blog-card"
            >
                <img
                    v-if="item.image"
                    :src="item.image"
                    :alt="item.title"
                    class="blog-card__image"
                    loading="lazy"
                />
                <div class="blog-card__body">
                    <div class="blog-card__meta">
                        <span v-if="item.category" class="blog-card__category">{{ item.category }}</span>
                        <span v-if="item.date" class="blog-card__date">{{ item.date }}</span>
                    </div>
                    <h2 class="blog-card__title">{{ item.title }}</h2>
                    <p v-if="item.excerpt" class="blog-card__excerpt">{{ item.excerpt }}</p>
                    <a
                        v-if="item.link_url"
                        class="blog-card__link"
                        :href="item.link_url"
                    >
                        Читать дальше →
                    </a>
                </div>
            </article>
        </section>
    </div>
</template>

<script setup>
/**
 * Шаблон блога (разводная): сетка карточек статей.
 * Источник данных — ACF repeater `blog_items` из группы group_blog.
 * Если repeater пуст — выводятся fallback-статьи.
 */
import { computed } from 'vue'

const props = defineProps({
    page: { type: Object, required: true },
    acf: { type: Object, default: () => ({}) },
    seo: { type: Object, default: () => ({}) },
    settings: { type: Object, default: () => ({}) },
    template: { type: String, default: '' },
})

const FALLBACK_ITEMS = [
    {
        title: 'Как спланировать гостиную с системой хранения: практичные идеи для квартиры',
        category: 'Интерьер',
        date: '14.08.2026',
        excerpt: 'Разбираем, как зонировать гостиную, выбрать шкафы-купе и встроенные полки, чтобы вещей стало меньше, а места — больше.',
        image: '/storage/uploads/portfolio/gotovyj-remont-2k-kvartiry-zhk-siberovo-kazan-17-800x600.jpg',
        link_url: '/blog/kak-splanirovat-gostinuyu-s-sistemoy-xraneniya',
    },
    {
        title: 'Ремонт квартиры с нуля: пошаговый план для собственника',
        category: 'Ремонт',
        date: '10.08.2026',
        excerpt: 'От чертежей и сметы до финальной уборки — все этапы ремонта квартиры в правильном порядке, с советами по экономии.',
        image: '/storage/uploads/portfolio/gotovyj-remont-dvuhkomnatnoj-kvartiry-zhk-merkurij-1-800x600.jpg',
        link_url: '/blog/remont-kvartiry-s-nulya-poshagovyy-plan',
    },
    {
        title: 'Почему канализацию в загородном доме лучше доверять мастерам: 5 аргументов',
        category: 'Инженерные системы',
        date: '05.08.2026',
        excerpt: 'Самостоятельный монтаж канализации в частном доме часто оборачивается протечками и переделками. Рассказываем, почему стоит обратиться к профессионалам.',
        image: '/storage/uploads/portfolio/Ремонт-ванны-1-800x600.jpg',
        link_url: '/blog/kanalizatsiya-v-zagorodnom-dome-5-argumentov',
    },
]

const items = computed(() => {
    const arr = props.acf?.blog_items
    if (Array.isArray(arr) && arr.length > 0) {
        return arr.map((item) => ({
            title: item.title || 'Без названия',
            category: item.category || '',
            date: item.date || '',
            excerpt: item.excerpt || '',
            image: item.image || '',
            link_url: item.link_url || '',
        }))
    }
    return FALLBACK_ITEMS
})
</script>

<style scoped>
.page--blog {
    max-width: 1120px;
    margin: 0 auto;
    padding: 56px 20px 80px;
}

.blog-hero {
    margin-bottom: 32px;
}

.blog-hero h1 {
    margin: 0 0 8px;
    font-size: 2rem;
    color: #111827;
    line-height: 1.2;
}

.blog-hero__intro {
    color: #4b5563;
    font-size: 1.1rem;
    margin: 0;
    line-height: 1.6;
}

.blog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
}

.blog-card {
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    overflow: hidden;
    background: #fff;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.blog-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
}

.blog-card__image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
    background: #f3f4f6;
}

.blog-card__body {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.blog-card__meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 10px;
}

.blog-card__category {
    display: inline-block;
    padding: 4px 10px;
    background: #eef2ff;
    color: #2563eb;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.blog-card__date {
    font-size: 0.85rem;
    color: #6b7280;
}

.blog-card__title {
    margin: 0 0 10px;
    font-size: 1.1rem;
    line-height: 1.35;
    color: #111827;
}

.blog-card__excerpt {
    margin: 0 0 16px;
    color: #4b5563;
    font-size: 0.95rem;
    line-height: 1.55;
    flex: 1;
}

.blog-card__link {
    display: inline-block;
    padding: 10px 18px;
    background: #2563eb;
    color: #fff;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    text-align: center;
    transition: background 0.15s ease;
}

.blog-card__link:hover {
    background: #1d4ed8;
}

@media (max-width: 640px) {
    .blog-grid {
        grid-template-columns: 1fr;
    }
}
</style>