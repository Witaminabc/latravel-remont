<template>
    <section v-if="items.length" class="tariffs">
        <div class="tariffs__inner">
            <h2 class="tariffs__heading">{{ headingText }}</h2>
        <p v-if="lead" class="tariffs__lead">{{ lead }}</p>

        <div class="tariffs__grid">
            <article
                v-for="(item, index) in items"
                :key="index"
                class="tariff"
                :class="{ 'is-highlight': item.highlight }"
            >
                <div
                    class="tariff__media"
                    :class="item.image ? null : `tariff__media--${index % 3}`"
                    :style="item.image ? { backgroundImage: `url('${item.image}')` } : null"
                >
                    <span v-if="!item.image" class="tariff__placeholder" aria-hidden="true">
                        {{ String(index + 1).padStart(2, '0') }}
                    </span>
                    <span v-if="item.term" class="tariff__term">{{ item.term }}</span>
                </div>

                <div class="tariff__body">
                    <h3 class="tariff__title">
                        <span class="tariff__title-main">{{ item.title }}</span>
                        <span v-if="item.subtitle" class="tariff__title-note">{{ item.subtitle }}</span>
                    </h3>

                    <p v-if="item.description" class="tariff__text">{{ item.description }}</p>

                    <ul v-if="item.features.length" class="tariff__features">
                        <li
                            v-for="(feature, featureIndex) in item.features"
                            :key="featureIndex"
                            class="tariff__feature"
                        >{{ feature }}</li>
                    </ul>

                    <p v-if="item.price !== null" class="tariff__price">
                        от <strong>{{ formatPrice(item.price) }}</strong> {{ item.priceUnit }}
                    </p>

                    <a v-if="item.linkUrl" class="tariff__button" :href="item.linkUrl">
                        {{ item.buttonLabel }}
                    </a>
                </div>
            </article>
        </div>

        <p v-if="note" class="tariffs__note">{{ note }}</p>
        </div>
    </section>
</template>

<script setup>
/**
 * Секция «Тарифы на ремонт квартир под ключ» главной страницы.
 *
 * Данные — ACF-группа group_home_tariffs: tariffs_heading, tariffs_lead,
 * tariffs (repeater), tariffs_note. Незаполненный ACF → демо-набор
 * FALLBACK_TARIFFS (по аналогии с FALLBACK_SECTIONS в PriceTable.vue):
 * значения из админки всегда приоритетнее.
 */
import { computed } from 'vue'

const props = defineProps({
    heading: { type: String, default: '' },
    lead: { type: String, default: '' },
    note: { type: String, default: '' },
    tariffs: { type: Array, default: () => [] },
})

const DEFAULT_HEADING = 'Тарифы на ремонт квартир под ключ'
const DEFAULT_PRICE_UNIT = 'руб/м²'
const DEFAULT_BUTTON_LABEL = 'Подробнее'

// Демо-данные со страницы-источника. Ссылки намеренно пустые: кнопка
// «Подробнее» появляется только после заполнения link_url в админке.
const FALLBACK_TARIFFS = [
    {
        title: 'Ремонт в новостройках',
        subtitle: '(White Box/Бетон)',
        term: 'Сроки от 30 дней',
        image: '',
        description: 'Доводим до идеала предчистовую отделку от застройщика. Выполняем финишную шпатлевку, поклейку обоев без видимых стыков, укладку ламината или кварцвинила с ровными зазорами под плинтус. Полностью обустраиваем ванную комнату под ключ: делаем обмазочную гидроизоляцию Церезит, укладываем плитку и керамогранит с заусовкой углов под 45 градусов. Производим частичный перенос и добавление розеток под мебель и кухонный гарнитур. В подарок: генеральный клининг квартиры перед сдачей ключей.',
        price: 9500,
        price_unit: DEFAULT_PRICE_UNIT,
        features: [],
        link_url: '',
        button_label: DEFAULT_BUTTON_LABEL,
        highlight: false,
    },
    {
        title: 'Капитальный ремонт «С нуля»',
        subtitle: '(Бетон/Кирпич)',
        term: 'Сроки от 45 дней',
        image: '',
        description: 'Полный технологический цикл отделки в новостройках без отделки или квартирах вторичного фонда. Включает выравнивание геометрии стен по маякам строго под 90° (чтобы мебель встала без щелей) и заливку прочной полусухой стяжки пола. Мы полностью заменяем ненадежную проводку от застройщика на медный кабель по ГОСТу с гарантией 5 лет, собираем электрощит с УЗО против ударов током и монтируем коллекторную разводку труб REHAU с автоматической защитой от протечек Neptun или Gidrolok. В подарок: послеремонтный клининг.',
        price: 14500,
        price_unit: DEFAULT_PRICE_UNIT,
        features: [],
        link_url: '',
        button_label: DEFAULT_BUTTON_LABEL,
        highlight: true,
    },
    {
        title: 'По готовому дизайн-проекту',
        subtitle: '(Индивидуальный)',
        term: 'Сроки от 75 дней',
        image: '',
        description: 'Профессиональная и точная реализация задумок вашего дизайнера строго по чертежам, планам и ведомостям материалов. Тариф рассчитан на сложные интерьерные решения: монтаж многоуровневых теневых и парящих потолков, установку скрытых дверей-невидимок (Invisible), скрытых трековых светильников, монтаж декоративных панелей, лепнины и укладку крупноформатного керамогранита. Все скрытые инженерные и отделочные этапы контролируются нашей внутренней службой технадзора.',
        price: 19500,
        price_unit: DEFAULT_PRICE_UNIT,
        features: [],
        link_url: '',
        button_label: DEFAULT_BUTTON_LABEL,
        highlight: false,
    },
]

const headingText = computed(() => props.heading || DEFAULT_HEADING)

function text(value) {
    if (typeof value === 'string') return value.trim()
    if (value === null || value === undefined) return ''
    return String(value).trim()
}

function normalize(row) {
    const raw = row && typeof row === 'object' ? row : {}
    const features = Array.isArray(raw.features) ? raw.features : []
    const price = Number(raw.price)
    const highlight = raw.highlight === true || raw.highlight === 1 || raw.highlight === '1'

    return {
        title: text(raw.title),
        subtitle: text(raw.subtitle),
        term: text(raw.term),
        image: text(raw.image),
        description: text(raw.description),
        price: Number.isFinite(price) && price > 0 ? price : null,
        priceUnit: text(raw.price_unit) || DEFAULT_PRICE_UNIT,
        features: features
            .map((feature) => (typeof feature === 'string' ? text(feature) : text(feature?.text)))
            .filter(Boolean),
        linkUrl: text(raw.link_url),
        buttonLabel: text(raw.button_label) || DEFAULT_BUTTON_LABEL,
        highlight,
    }
}

// Значения из ACF приоритетнее демо-набора; карточки без названия отбрасываем
const items = computed(() => {
    const source = Array.isArray(props.tariffs) && props.tariffs.length ? props.tariffs : FALLBACK_TARIFFS

    return source.filter((row) => text(row?.title)).map(normalize)
})

function formatPrice(price) {
    const value = Number(price)
    return Number.isFinite(value) ? value.toLocaleString('ru-RU') : '—'
}
</script>

<style scoped>
.tariffs {
    max-width: 1120px;
    margin: 0 auto;
    padding: 56px 20px 80px;
}

.tariffs__heading {
    margin: 0 0 40px;
    font-size: 2rem;
    text-align: center;
    color: #111827;
}

.tariffs__lead {
    max-width: 760px;
    margin: 0 auto 28px;
    color: #4b5563;
    text-align: center;
    line-height: 1.6;
    white-space: pre-line;
}

.tariffs__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
    align-items: stretch;
}

.tariff {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    background: #fff;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.15s ease;
}

.tariff:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(17, 24, 39, 0.08);
    border-color: #d1d5db;
}

.tariff.is-highlight {
    border-color: #2563eb;
    box-shadow: 0 16px 32px rgba(37, 99, 235, 0.16);
}

.tariff__media {
    position: relative;
    display: flex;
    align-items: flex-end;
    min-height: 200px;
    padding: 16px;
    background-color: #1f2937;
    background-image: linear-gradient(135deg, #1e3a8a, #0f172a);
    background-size: cover;
    background-position: center;
}

/* Свои заглушки вместо чужих фото: три спокойных градиента */
.tariff__media--0 {
    background-image: linear-gradient(135deg, #1e3a8a, #2563eb);
}

.tariff__media--1 {
    background-image: linear-gradient(135deg, #0f766e, #14b8a6);
}

.tariff__media--2 {
    background-image: linear-gradient(135deg, #9a3412, #f59e0b);
}

.tariff__placeholder {
    position: absolute;
    top: 8px;
    right: 16px;
    color: rgba(255, 255, 255, 0.35);
    font-size: 3rem;
    font-weight: 700;
    line-height: 1;
}

.tariff__term {
    position: relative;
    z-index: 1;
    padding: 4px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.92);
    color: #111827;
    font-size: 0.8rem;
    font-weight: 600;
}

.tariff__body {
    display: flex;
    flex: 1;
    flex-direction: column;
    padding: 22px;
}

.tariff__title {
    margin: 0 0 12px;
    font-size: 1.2rem;
    line-height: 1.35;
}

.tariff__title-main {
    display: block;
}

.tariff__title-note {
    display: block;
    color: #6b7280;
    font-size: 0.95rem;
    font-weight: 500;
}

.tariff__text {
    margin: 0 0 14px;
    color: #4b5563;
    line-height: 1.6;
    white-space: pre-line;
}

.tariff__features {
    margin: 0 0 16px;
    padding: 0;
    list-style: none;
}

.tariff__feature {
    position: relative;
    margin-bottom: 6px;
    padding-left: 22px;
    color: #374151;
    font-size: 0.95rem;
    line-height: 1.5;
}

.tariff__feature::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: #047857;
    font-weight: 700;
}

.tariff__price {
    margin: auto 0 16px;
    color: #111827;
    font-size: 1rem;
}

.tariff__price strong {
    font-size: 1.45rem;
    white-space: nowrap;
}

.tariff.is-highlight .tariff__price strong {
    color: #1d4ed8;
}

.tariff__button {
    display: inline-block;
    align-self: flex-start;
    padding: 12px 22px;
    border-radius: 12px;
    background: #2563eb;
    color: #fff;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.15s ease;
}

.tariff__button:hover {
    background: #1d4ed8;
}

.tariffs__note {
    margin: 24px 0 0;
    padding: 20px;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    background: #f9fafb;
    color: #374151;
    line-height: 1.6;
    white-space: pre-line;
}

@media (max-width: 720px) {
    .tariffs {
        padding: 40px 16px 56px;
    }

    .tariffs__heading {
        font-size: 1.6rem;
    }

    .tariff__media {
        min-height: 170px;
    }
}
</style>

