<template>
    <div class="page page--home" :class="`layout--${layout}`">
        <template v-if="blocksOn">
            <!--            <Hero-->
<!--                :title="acf.hero_title || page.title"-->
<!--                :subtitle="acf.hero_subtitle"-->
<!--                :text="acf.hero_text"-->
<!--                :image="heroImage"-->
<!--                :rating="acf.rating"-->
<!--            />-->
<!--            <section v-if="page.content" class="home-content">-->
<!--                <div v-html="page.content"></div>-->
<!--            </section>-->
<!--            <Features v-if="features.length" :items="features" />-->

            <BannerSlider :slides="bannerSlides" />


            <Tariffs
                v-if="tariffsOn"
                :heading="acf.tariffs_heading"
                :lead="acf.tariffs_lead"
                :note="acf.tariffs_note"
                :tariffs="tariffs"
            />

            <PriceTable :sections="priceSections" />
            <Masters v-if="mastersOn" :heading="acf.masters_heading" :masters="masters" />
            <Reviews v-if="reviewsOn" :reviews="reviews" />
            <RequestForm v-if="formOn" :heading="acf.form_heading" :subtitle="acf.form_subtitle" :button-label="acf.form_button_label" />

            <!-- Квиз «Узнайте стоимость ремонта» — в конце страницы, перед подвалом -->
            <QuizForm
                v-if="quizOn"
                :heading="acf.quiz_heading"
                :lead="acf.quiz_lead"
                :bonuses-title="acf.quiz_bonuses_title"
                :bonuses="acf.quiz_bonuses || []"
                :steps="acf.quiz_steps || []"
                :contacts-title="acf.quiz_contacts_title"
                :contacts-description="acf.quiz_contacts_description"
                :name-label="acf.quiz_name_label"
                :phone-label="acf.quiz_phone_label"
                :email-label="acf.quiz_email_label"
                :consent-text="acf.quiz_consent_text"
                :terms-url="acf.quiz_terms_url"
                :privacy-url="acf.quiz_privacy_url"
                :next-label="acf.quiz_button_next"
                :prev-label="acf.quiz_button_prev"
                :submit-label="acf.quiz_button_submit"
                :error-text="acf.quiz_error_text"
                :sending-text="acf.quiz_sending_text"
                :submit-error-text="acf.quiz_error_submit_text"
                :success-title="acf.quiz_success_title"
                :success-text="acf.quiz_success_text"
            />
        </template>


    </div>
</template>

<script setup>
/**
 * Шаблон главной страницы.
 * Секции: Hero (group_home), Баннер-слайдер, Преимущества, Тарифы
 * (group_home_tariffs), Прайс-лист (price_sections), квиз (group_home_quiz),
 * контент страницы.
 */
import { computed } from 'vue'
import Hero from './blocks/Hero.vue'
import BannerSlider from './blocks/BannerSlider.vue'
import Features from './blocks/Features.vue'
import Tariffs from './blocks/Tariffs.vue'
import PriceTable from './blocks/PriceTable.vue'
import Masters from './blocks/Masters.vue'
import Reviews from './blocks/Reviews.vue'
import RequestForm from './blocks/RequestForm.vue'
import QuizForm from './blocks/QuizForm.vue'

const props = defineProps({
    page: { type: Object, required: true },
    acf: { type: Object, default: () => ({}) },
    seo: { type: Object, default: () => ({}) },
    settings: { type: Object, default: () => ({}) },
    template: { type: String, default: '' },
})

const heroImage = computed(() => props.acf?.hero_image || props.page?.featured_image_url || null)

const layout = computed(() => props.acf?.layout || 'classic')

// Выключение блока в админке: meta.blocks_enabled.group_home (по умолчанию включён)
const blocksOn = computed(() => props.page?.meta?.blocks_enabled?.group_home !== false)

const features = computed(() => {
    const items = props.acf?.features
    if (!Array.isArray(items)) return []

    return items.filter((item) => item && (item.title || item.description))
})

// Баннер-слайдер: repeater banner_slides из группы group_home
const bannerSlides = computed(() => {
    const slides = props.acf?.banner_slides
    return Array.isArray(slides) ? slides : []
})

// Прайс-лист: разделы из ACF (price_sections); пусто → тестовые данные в PriceTable
const priceSections = computed(() => {
    const sections = props.acf?.price_sections
    return Array.isArray(sections) ? sections : []
})

// Тарифы: выключение блока в админке — meta.blocks_enabled.group_home_tariffs
const tariffsOn = computed(() => props.page?.meta?.blocks_enabled?.group_home_tariffs !== false)

const tariffs = computed(() => {
    const items = props.acf?.tariffs
    return Array.isArray(items) ? items : []
})

// Выключение блока мастера в админке: meta.blocks_enabled.group_home_masters (по умолчанию включён)
const mastersOn = computed(() => props.page?.meta?.blocks_enabled?.group_home_masters !== false)

const masters = computed(() => {
    const items = props.acf?.masters
    return Array.isArray(items) ? items : []
})

// Отзывы: выключение блока в админке — acf.show_reviews (по умолчанию true)
const reviewsOn = computed(() => props.acf?.show_reviews !== false)

const reviews = computed(() => {
    const items = props.acf?.reviews
    return Array.isArray(items) ? items : []
})

// Форма заявки: выключение через acf.show_form (по умолчанию включена)
const formOn = computed(() => props.acf?.show_form !== false)

// Квиз: выключение блока в админке — meta.blocks_enabled.group_home_quiz
const quizOn = computed(() => props.page?.meta?.blocks_enabled?.group_home_quiz !== false)
</script>

<style scoped>
.page--home {
    display: block;
}

.home-content {
    max-width: 960px;
    margin: 0 auto;
    padding: 24px 20px 64px;
    font-size: 1.05rem;
    line-height: 1.7;
}

.home-content :deep(img) {
    max-width: 100%;
    height: auto;
}
</style>
