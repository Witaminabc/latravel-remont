<template>
    <section class="price-table">
        <h2 class="price-table__heading">Прайс-лист на ремонт квартир</h2>

        <div
            v-for="(section, sIndex) in visibleSections"
            :key="sIndex"
            class="price-table__section"
            :class="{ 'is-open': activeIndex === sIndex }"
        >
            <button type="button" class="price-table__title" @click="toggle(sIndex)">
                <span>{{ section.title }}</span>
                <i class="price-table__chevron pi" :class="activeIndex === sIndex ? 'pi-chevron-up' : 'pi-chevron-down'"></i>
            </button>

            <div v-show="activeIndex === sIndex" class="price-table__body">
                <template v-if="section.subsections && section.subsections.length">
                    <div
                        v-for="(sub, subIndex) in section.subsections"
                        :key="subIndex"
                        class="price-table__subsection"
                    >
                        <h4 v-if="sub.title" class="price-table__subtitle">{{ sub.title }}</h4>

                        <div class="price-table__scroll">
                            <table class="price-table__table">
                                <thead>
                                    <tr>
                                        <th>Вид работ</th>
                                        <th class="price-table__col-unit">Ед.</th>
                                        <th class="price-table__col-price">Цена, ₽</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(row, rIndex) in sub.rows" :key="rIndex">
                                        <td>{{ row.name }}</td>
                                        <td class="price-table__col-unit">{{ row.unit }}</td>
                                        <td class="price-table__col-price">{{ formatPrice(row.price) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <div v-else class="price-table__scroll">
                    <table class="price-table__table">
                        <thead>
                            <tr>
                                <th>Вид работ</th>
                                <th class="price-table__col-unit">Ед.</th>
                                <th class="price-table__col-price">Цена, ₽</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, rIndex) in section.rows" :key="rIndex">
                                <td>{{ row.name }}</td>
                                <td class="price-table__col-unit">{{ row.unit }}</td>
                                <td class="price-table__col-price">{{ formatPrice(row.price) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <p class="price-table__note">
            Итоговая стоимость зависит от объёма и сложности работ — рассчитаем бесплатно после осмотра.
        </p>
    </section>
</template>

<script setup>
/**
 * Секция «Прайс-лист» главной страницы (ACF-поле price_sections).
 * Структура (3 уровня): [{ title, subsections: [{ title, rows: [{ name, unit, price }] }] }].
 * Совместимость: если у раздела нет subsections, но есть rows — рендерим строки напрямую.
 * Если значения из ACF пусты — показываются тестовые данные.
 */
import { computed, ref } from 'vue'

const props = defineProps({
    sections: { type: Array, default: () => [] },
})

// Аккордеон: открыт только один раздел. null — все закрыты.
const activeIndex = ref(null)

function toggle(index) {
    activeIndex.value = activeIndex.value === index ? null : index
}

// Тестовые данные (если ACF пусто): один раздел с подразделами + один без.
const FALLBACK_SECTIONS = [
    {
        title: 'Демонтажные работы',
        subsections: [
            {
                title: '1. Потолок',
                rows: [
                    { name: 'Разбор подвесного потолка (Армстронг)', unit: 'м²', price: 300 },
                    { name: 'Демонтаж полимерного покрытия с потолка', unit: 'м²', price: 250 },
                    { name: 'Удаление обоев', unit: 'м²', price: 240 },
                ],
            },
        ],
    },
    {
        title: 'Электромонтажные работы',
        subsections: [],
        rows: [
            { name: 'Установка розетки', unit: 'шт', price: 200 },
            { name: 'Установка подрозетника', unit: 'шт', price: 150 },
            { name: 'Разводка кабеля', unit: 'м.п', price: 90 },
        ],
    },
]

const visibleSections = computed(() => {
    const fromAcf = (props.sections ?? [])
        .map((section) => {
            const title = section?.title ?? ''
            const subs = Array.isArray(section?.subsections) ? section.subsections : []
            const cleanSubs = subs
                .map((sub) => ({
                    title: sub?.title ?? '',
                    rows: (Array.isArray(sub?.rows) ? sub.rows : []).filter((row) => row && row.name),
                }))
                .filter((sub) => sub.rows.length)

            // Обратная совместимость со старым форматом: строки прямо в разделе
            const directRows = (Array.isArray(section?.rows) ? section.rows : []).filter((row) => row && row.name)

            return { title, subsections: cleanSubs, rows: directRows }
        })
        .filter((section) => section.title && (section.subsections.length || section.rows.length))

    return fromAcf.length ? fromAcf : FALLBACK_SECTIONS
})

function formatPrice(price) {
    const value = Number(price)
    return Number.isFinite(value) ? value.toLocaleString('ru-RU') : '—'
}
</script>

<style scoped>
.price-table {
    max-width: 1120px;
    margin: 0 auto;
    padding: 56px 20px 80px;
}

.price-table__heading {
    margin: 0 0 40px;
    font-size: 2rem;
    text-align: center;
    color: #111827;
}

.price-table__section {
    margin-bottom: 32px;
}

.price-table__title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    margin: 0 0 12px;
    padding: 14px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    background: #f3f4f6;
    color: #111827;
    font-size: 1.15rem;
    font-weight: 600;
    text-align: left;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}

.price-table__title:hover {
    background: #e5e7eb;
}

.price-table__section.is-open .price-table__title {
    border-color: #2563eb;
    background: #eff6ff;
    color: #1d4ed8;
    margin-bottom: 0;
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
}

.price-table__chevron {
    font-size: 0.9rem;
    color: #6b7280;
    transition: transform 0.15s ease;
}

.price-table__section.is-open .price-table__chevron {
    color: #2563eb;
}

.price-table__body {
    padding: 16px 0 8px;
}

.price-table__section.is-open .price-table__body {
    border: 1px solid #2563eb;
    border-top: none;
    border-bottom-left-radius: 16px;
    border-bottom-right-radius: 16px;
    padding: 16px;
}

.price-table__subsection {
    margin-bottom: 20px;
}

.price-table__subtitle {
    margin: 0 0 8px;
    padding-left: 4px;
    color: #2563eb;
    font-size: 1rem;
    font-weight: 600;
}

.price-table__scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.price-table__table {
    width: 100%;
    min-width: 480px;
    border-collapse: collapse;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
}

.price-table__table th,
.price-table__table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.price-table__table thead th {
    background: #f9fafb;
    color: #6b7280;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.price-table__table tbody tr:last-child td {
    border-bottom: none;
}

.price-table__table tbody tr:hover {
    background: #f9fafb;
}

.price-table__col-unit {
    width: 80px;
    color: #6b7280;
}

.price-table__col-price {
    width: 120px;
    text-align: right !important;
    font-weight: 600;
    white-space: nowrap;
}

.price-table__note {
    margin: 8px 0 0;
    color: #6b7280;
    font-size: 0.95rem;
    text-align: center;
}

@media (max-width: 720px) {
    .price-table {
        padding: 40px 16px 56px;
    }

    .price-table__heading {
        font-size: 1.6rem;
        margin-bottom: 28px;
    }
}
</style>
