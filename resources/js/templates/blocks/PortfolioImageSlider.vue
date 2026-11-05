<template>
    <section
        v-if="slides.length"
        class="portfolio-slider"
        :class="`portfolio-slider--${variant}`"
        @mouseenter="pause"
        @mouseleave="resume"
    >
        <div class="portfolio-slider__viewport">
            <div class="portfolio-slider__track" :style="{ transform: `translateX(-${active * 100}%)` }">
                <img
                    v-for="(src, index) in slides"
                    :key="`${index}-${src}`"
                    class="portfolio-slider__slide"
                    :style="height ? { height: height + 'px' } : null"
                    :src="src"
                    :alt="`${title || 'Фото объекта'} — фото ${index + 1}`"
                    :aria-hidden="index !== active"
                    loading="lazy"
                />
            </div>
        </div>

        <template v-if="slides.length > 1">
            <span class="portfolio-slider__counter">{{ active + 1 }} / {{ slides.length }}</span>

            <button
                type="button"
                class="portfolio-slider__nav portfolio-slider__nav--prev"
                aria-label="Предыдущее фото"
                @click="prev"
            >‹</button>
            <button
                type="button"
                class="portfolio-slider__nav portfolio-slider__nav--next"
                aria-label="Следующее фото"
                @click="next"
            >›</button>

            <div class="portfolio-slider__dots">
                <button
                    v-for="(src, index) in slides"
                    :key="`dot-${index}`"
                    type="button"
                    class="portfolio-slider__dot"
                    :class="{ 'is-active': index === active }"
                    :aria-label="`Фото ${index + 1}`"
                    @click="goTo(index)"
                ></button>
            </div>
        </template>
    </section>
</template>

<script setup>
/**
 * Слайдер фотографий объекта портфолио.
 * Используется на детальной странице (variant="default") и в карточках
 * разводной страницы (variant="compact").
 *
 * images принимает оба формата ACF:
 *  - массив строк: ['/storage/uploads/...jpg']
 *  - массив строк репитера: [{ image: '/storage/uploads/...jpg' }]
 */
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps({
    images: { type: Array, default: () => [] },
    title: { type: String, default: '' },
    variant: { type: String, default: 'default' }, // 'default' | 'compact'
    height: { type: Number, default: 0 }, // 0 — высота из CSS варианта
    autoplay: { type: Boolean, default: true },
    interval: { type: Number, default: 6000 },
})

const active = ref(0)
let timer = null

const slides = computed(() => {
    const list = Array.isArray(props.images) ? props.images : []
    const urls = list
        .map((item) => {
            if (typeof item === 'string') return item
            if (item && typeof item === 'object') return item.image || item.url || ''
            return ''
        })
        .filter((src) => typeof src === 'string' && src.trim() !== '')

    return Array.from(new Set(urls))
})

const canLoop = computed(() => slides.value.length > 1)

const stop = () => {
    if (timer) {
        clearInterval(timer)
        timer = null
    }
}

const start = () => {
    stop()
    if (!props.autoplay || !canLoop.value) return

    timer = setInterval(() => {
        active.value = (active.value + 1) % slides.value.length
    }, Math.max(1500, props.interval))
}

const pause = () => stop()
const resume = () => start()

const prev = () => {
    if (!slides.value.length) return
    active.value = (active.value - 1 + slides.value.length) % slides.value.length
}

const next = () => {
    if (!slides.value.length) return
    active.value = (active.value + 1) % slides.value.length
}

const goTo = (index) => {
    if (index < 0 || index >= slides.value.length) return
    active.value = index
}

watch(slides, () => {
    if (active.value >= slides.value.length) active.value = 0
    start()
})

watch(() => [props.autoplay, props.interval], start)

onMounted(start)
onBeforeUnmount(stop)

// Позволяем родителю переключать слайды (миниатюры под слайдером)
defineExpose({ active, goTo, next, prev })
</script>

<style scoped>
.portfolio-slider { position: relative; margin-bottom: 24px; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; background: #1f2937; }
.portfolio-slider__viewport { overflow: hidden; }
.portfolio-slider__track { display: flex; transition: transform 0.45s ease; width: 100%; }
.portfolio-slider__slide { flex: 0 0 100%; width: 100%; height: 440px; object-fit: cover; display: block; }
.portfolio-slider__counter { position: absolute; top: 12px; right: 12px; z-index: 2; padding: 4px 10px; border-radius: 999px; background: rgba(17, 24, 39, 0.65); color: #fff; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.02em; }
.portfolio-slider__nav { position: absolute; top: 50%; transform: translateY(-50%); width: 44px; height: 44px; border: none; border-radius: 50%; background: rgba(255, 255, 255, 0.85); color: #111827; font-size: 1.6rem; line-height: 1; cursor: pointer; transition: background 0.15s ease; z-index: 2; display: flex; align-items: center; justify-content: center; padding: 0; }
.portfolio-slider__nav:hover { background: #fff; }
.portfolio-slider__nav--prev { left: 12px; }
.portfolio-slider__nav--next { right: 12px; }
.portfolio-slider__dots { position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 2; }
.portfolio-slider__dot { width: 10px; height: 10px; padding: 0; border: none; border-radius: 999px; background: rgba(255, 255, 255, 0.5); cursor: pointer; transition: width 0.2s ease, background 0.2s ease; }
.portfolio-slider__dot.is-active { width: 26px; background: #2563eb; }

/* Компактный вариант — слайдер внутри карточки на разводной странице */
.portfolio-slider--compact { margin-bottom: 0; border: none; border-radius: 0; }
.portfolio-slider--compact .portfolio-slider__slide { height: 200px; }
.portfolio-slider--compact .portfolio-slider__counter { top: 8px; right: 8px; padding: 2px 8px; font-size: 0.7rem; }
.portfolio-slider--compact .portfolio-slider__nav { width: 32px; height: 32px; font-size: 1.1rem; }
.portfolio-slider--compact .portfolio-slider__nav--prev { left: 6px; }
.portfolio-slider--compact .portfolio-slider__nav--next { right: 6px; }
.portfolio-slider--compact .portfolio-slider__dots { bottom: 8px; gap: 6px; }
.portfolio-slider--compact .portfolio-slider__dot { width: 7px; height: 7px; }
.portfolio-slider--compact .portfolio-slider__dot.is-active { width: 18px; }

@media (max-width: 720px) {
    .portfolio-slider__slide { height: 280px; }
    .portfolio-slider__nav { display: none; }
    .portfolio-slider--compact .portfolio-slider__slide { height: 180px; }
}
</style>