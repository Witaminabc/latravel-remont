<template>
    <div class="page page--portfolio-item">
        <section class="portfolio-item-hero">
            <a v-if="acf.item_link_back" class="portfolio-item__back" :href="acf.item_link_back">← Назад к портфолио</a>
            <h1 class="portfolio-item__title">{{ acf.item_title || page.title }}</h1>
            <p v-if="acf.item_address" class="portfolio-item__meta">{{ acf.item_address }}</p>
            <div class="portfolio-item__stats">
                <span v-if="acf.item_area" class="portfolio-item__stat">Площадь: {{ acf.item_area }} м²</span>
                <span v-if="acf.item_term" class="portfolio-item__stat">Сроки: {{ acf.item_term }} дней</span>
                <span v-if="acf.item_price_works" class="portfolio-item__stat">Работы: {{ formatPrice(acf.item_price_works) }} руб.</span>
                <span v-if="acf.item_price_materials" class="portfolio-item__stat">Материалы: {{ formatPrice(acf.item_price_materials) }} руб.</span>
            </div>
        </section>

        <section v-if="sliderImages.length" class="portfolio-item-image">
            <PortfolioImageSlider
                ref="sliderRef"
                :images="sliderImages"
                :title="acf.item_title"
            />

            <div v-if="sliderImages.length > 1" class="portfolio-item-image__thumbs">
                <button
                    v-for="(img, idx) in sliderImages"
                    :key="`thumb-${idx}`"
                    type="button"
                    class="portfolio-item-image__thumb-btn"
                    :class="{ 'is-active': sliderRef?.active === idx }"
                    :aria-label="`Показать фото ${idx + 1}`"
                    @click="sliderRef?.goTo(idx)"
                >
                    <img :src="img" :alt="`${acf.item_title} миниатюра ${idx + 1}`" class="portfolio-item-image__thumb" />
                </button>
            </div>
        </section>

        <section v-if="acf.item_description" class="portfolio-item-description">
            <h2>Описание объекта</h2>
            <div v-html="acf.item_description" class="portfolio-item-description__text"></div>
        </section>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import PortfolioImageSlider from './blocks/PortfolioImageSlider.vue'

const props = defineProps({
    page: { type: Object, required: true },
    acf: { type: Object, default: () => ({}) },
    seo: { type: Object, default: () => ({}) },
    settings: { type: Object, default: () => ({}) },
    template: { type: String, default: '' },
})

const sliderRef = ref(null)

const FORMATTER = new Intl.NumberFormat('ru-RU')
const formatPrice = (value) => value !== null && value !== undefined ? FORMATTER.format(value) : '—'

/**
 * ACF-репитер может вернуть как строки, так и объекты { image: '...' }.
 * Приводим оба варианта к массиву url.
 */
const toUrl = (item) => {
    if (typeof item === 'string') return item
    if (item && typeof item === 'object') return item.image || item.url || ''
    return ''
}

const galleryImages = computed(() => {
    const list = Array.isArray(props.acf?.item_gallery) ? props.acf.item_gallery : []
    return list.map(toUrl).filter((src) => typeof src === 'string' && src.trim() !== '')
})

const sliderImages = computed(() => {
    const imgs = []
    const main = toUrl(props.acf?.item_image)

    if (main) imgs.push(main)
    for (const src of galleryImages.value) {
        if (src !== main && !imgs.includes(src)) imgs.push(src)
    }

    return imgs
})
</script>

<style scoped>
.page--portfolio-item { max-width: 1120px; margin: 0 auto; padding: 56px 20px 80px; }
.portfolio-item-hero { margin-bottom: 24px; }
.portfolio-item__back { display: inline-block; margin-bottom: 12px; color: #2563eb; text-decoration: none; font-size: 0.95rem; }
.portfolio-item__back:hover { text-decoration: underline; }
.portfolio-item__title { margin: 0 0 8px; font-size: 2rem; }
.portfolio-item__meta { margin: 0 0 12px; color: #6b7280; font-size: 1rem; }
.portfolio-item__stats { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; color: #374151; font-size: 0.95rem; }
.portfolio-item__stat { display: flex; align-items: center; gap: 4px; }
.portfolio-item-image__thumbs { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-top: 12px; }
.portfolio-item-image__thumb-btn { padding: 0; border: 2px solid transparent; border-radius: 8px; background: none; cursor: pointer; line-height: 0; transition: border-color 0.15s ease, opacity 0.15s ease; opacity: 0.75; }
.portfolio-item-image__thumb-btn:hover { opacity: 1; border-color: #93c5fd; }
.portfolio-item-image__thumb-btn.is-active { opacity: 1; border-color: #2563eb; }
.portfolio-item-image__thumb { width: 90px; height: 66px; object-fit: cover; border-radius: 6px; display: block; }
.portfolio-item-description h2 { margin: 0 0 12px; font-size: 1.5rem; }
.portfolio-item-description__text { color: #4b5563; line-height: 1.7; }
</style>