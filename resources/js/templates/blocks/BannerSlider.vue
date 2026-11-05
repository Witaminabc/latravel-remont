<template>
    <section v-if="filteredSlides.length" class="banner-slider" @mouseenter="pause" @mouseleave="resume">
        <div class="banner-slider__viewport">
            <div class="banner-slider__track" :style="{ transform: `translateX(-${active * 100}%)` }">
                <article
                    v-for="(slide, index) in filteredSlides"
                    :key="index"
                    class="banner-slider__slide"
                    :style="slide.image ? { backgroundImage: `url('${slide.image}')` } : null"
                    :aria-hidden="index !== active"
                >
                    <div class="banner-slider__overlay"></div>
                    <div class="banner-slider__content">
                        <span v-if="slide.badge" class="banner-slider__badge">{{ slide.badge }}</span>
                        <h2 v-if="slide.title" class="banner-slider__title">{{ slide.title }}</h2>
                        <p v-if="slide.subtitle" class="banner-slider__subtitle">{{ slide.subtitle }}</p>
                        <p v-if="slide.text" class="banner-slider__text">{{ slide.text }}</p>
                        <a
                            v-if="slide.button_label"
                            class="banner-slider__button"
                            :href="slide.button_url || '#'"
                        >{{ slide.button_label }}</a>
                    </div>
                </article>
            </div>
        </div>

        <template v-if="filteredSlides.length > 1">
            <button
                type="button"
                class="banner-slider__nav banner-slider__nav--prev"
                aria-label="Предыдущий слайд"
                @click="prev"
            >‹</button>
            <button
                type="button"
                class="banner-slider__nav banner-slider__nav--next"
                aria-label="Следующий слайд"
                @click="next"
            >›</button>

            <div class="banner-slider__dots">
                <button
                    v-for="(slide, index) in filteredSlides"
                    :key="index"
                    type="button"
                    class="banner-slider__dot"
                    :class="{ 'is-active': index === active }"
                    :aria-label="`Слайд ${index + 1}`"
                    @click="goTo(index)"
                ></button>
            </div>
        </template>
    </section>
</template>

<script setup>
/**
 * Секция «Баннер-слайдер» главной страницы (ACF-поле banner_slides, repeater).
 * Слайды: title, subtitle, text, badge, button_label, button_url, image.
 */
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
    slides: { type: Array, default: () => [] },
})

const AUTOPLAY_MS = 6000

const active = ref(0)
let timer = null

const FALLBACK_SLIDES = [
    {
        badge: 'Акция',
        title: 'Ремонт квартир под ключ',
        subtitle: 'Быстро и с гарантией',
        text: 'Качественная отделка, прозрачная смета и соблюдение сроков. Работаем по договору.',
        button_label: 'Оставить заявку',
        button_url: '#',
        image: '',
    },
    {
        badge: 'Новинка',
        title: 'Дизайн-проект в подарок',
        subtitle: 'При заказе ремонта',
        text: 'Разработаем индивидуальный проект с 3D-визуализацией и подбором материалов.',
        button_label: 'Узнать подробнее',
        button_url: '#',
        image: '',
    },
    {
        badge: 'Гарантия',
        title: 'Гарантия на работы 3 года',
        subtitle: 'Надёжность и контроль качества',
        text: 'Все этапы принимаются по чек-листу, материалы с сертификатами и гарантией производителя.',
        button_label: 'Подробнее',
        button_url: '#',
        image: '',
    },
    {
        badge: 'Сроки',
        title: 'Сдача объекта в срок',
        subtitle: 'Чёткий график работ',
        text: 'Фиксируем сроки в договоре, еженедельный отчёт и фотофиксация прогресса.',
        button_label: 'Узнать сроки',
        button_url: '#',
        image: '',
    },
    {
        badge: 'Цена',
        title: 'Прозрачная смета',
        subtitle: 'Без скрытых платежей',
        text: 'Детализированная смета до начала работ, согласование любых изменений.',
        button_label: 'Получить смету',
        button_url: '#',
        image: '',
    },
]

const filteredSlides = computed(() => {
    const source = Array.isArray(props.slides) && props.slides.length
        ? props.slides
        : FALLBACK_SLIDES
    return source.filter(
        (slide) => slide && (slide.title || slide.subtitle || slide.text || slide.badge || slide.image)
    )
})

const count = computed(() => filteredSlides.value.length)

function goTo(index) {
    if (!count.value) return
    active.value = (index + count.value) % count.value
}

function next() {
    goTo(active.value + 1)
}

function prev() {
    goTo(active.value - 1)
}

function start() {
    stop()
    if (count.value > 1) {
        timer = setInterval(next, AUTOPLAY_MS)
    }
}

function stop() {
    if (timer) {
        clearInterval(timer)
        timer = null
    }
}

function pause() {
    stop()
}

function resume() {
    start()
}

onMounted(start)
onBeforeUnmount(stop)
</script>

<style scoped>
.banner-slider {
    position: relative;
    max-width: 1120px;
    margin: 0 auto;
    padding: 56px 20px 80px;
}

.banner-slider__viewport {
    overflow: hidden;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(17, 24, 39, 0.12);
}

.banner-slider__track {
    display: flex;
    transition: transform 0.45s ease;
}

.banner-slider__slide {
    position: relative;
    flex: 0 0 100%;
    min-height: 420px;
    display: flex;
    align-items: flex-end;
    background-color: #1f2937;
    background-image: linear-gradient(120deg, #1e3a8a, #0f172a);
    background-size: cover;
    background-position: center;
}

.banner-slider__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(15, 23, 42, 0.85) 0%, rgba(15, 23, 42, 0.35) 55%, rgba(15, 23, 42, 0.15) 100%);
}

.banner-slider__content {
    position: relative;
    z-index: 1;
    max-width: 560px;
    padding: 40px;
    color: #fff;
}

.banner-slider__badge {
    display: inline-block;
    margin: 0 0 14px;
    padding: 4px 12px;
    border-radius: 999px;
    background: #f59e0b;
    color: #111827;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.banner-slider__title {
    margin: 0 0 12px;
    font-size: 2rem;
    line-height: 1.15;
}

.banner-slider__subtitle {
    margin: 0 0 14px;
    font-size: 1.2rem;
    color: #dbeafe;
}

.banner-slider__text {
    margin: 0 0 22px;
    font-size: 1rem;
    line-height: 1.65;
    color: #cbd5e1;
    white-space: pre-line;
}

.banner-slider__button {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 12px;
    background: #2563eb;
    color: #fff;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.15s ease;
}

.banner-slider__button:hover {
    background: #1d4ed8;
}

.banner-slider__nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 44px;
    height: 44px;
    border: none;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.85);
    color: #111827;
    font-size: 1.6rem;
    line-height: 1;
    cursor: pointer;
    transition: background 0.15s ease;
    z-index: 2;
}

.banner-slider__nav:hover {
    background: #fff;
}

.banner-slider__nav--prev {
    left: 12px;
}

.banner-slider__nav--next {
    right: 12px;
}

.banner-slider__dots {
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 8px;
    z-index: 2;
}

.banner-slider__dot {
    width: 10px;
    height: 10px;
    padding: 0;
    border: none;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: width 0.2s ease, background 0.2s ease;
}

.banner-slider__dot.is-active {
    width: 26px;
    background: #fff;
}

@media (max-width: 720px) {
    .banner-slider {
        padding: 40px 16px 56px;
    }

    .banner-slider__slide {
        min-height: 320px;
    }

    .banner-slider__content {
        padding: 28px 20px;
    }

    .banner-slider__title {
        font-size: 1.6rem;
    }

    .banner-slider__nav {
        display: none;
    }
}
</style>
