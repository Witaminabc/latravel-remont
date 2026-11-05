<template>
    <section class="masters" v-if="masters.length">
        <h2 class="masters__heading">{{ heading || 'Штат мастеров нашей ремонтной компании' }}</h2>
        <div class="masters__blocks">
            <div class="masters__block"
                 v-for="(item, index) in masters"
                 :key="index"
            >
                <img v-if="item.image" :src="item.image" :alt="item.name" class="masters__block-img"/>
                <div v-else class="masters__block-placeholder" :aria-label="item.name">
                    {{ String(index + 1).padStart(2, '0') }}
                </div>
                <div class="masters__block-info">
                    <div class="masters__block-name">{{ item.name }}</div>
                    <div class="masters__block-specialty" v-if="item.specialty">
                        {{ item.specialty }}
                    </div>
                    <div class="masters__block-experience" v-if="item.experience !== null && item.experience !== ''">
                        стаж {{ item.experience }} {{ experienceWord(item.experience) }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    heading: { type: String, default: '' },
    masters: { type: Array, default: () => [] },
})

const FALLBACK_MASTERS = [
    { name: 'Николай', specialty: 'плиточник-облицовщик', experience: 12, image: '' },
    { name: 'Алексей', specialty: 'электрик', experience: 9, image: '' },
    { name: 'Дмитрий', specialty: 'сантехник', experience: 15, image: '' },
    { name: 'Сергей', specialty: 'отделочник', experience: 7, image: '' },
]

// Если ACF не заполнен — показываем демо-заглушки.
const displayMasters = computed(() => {
    const items = props.masters
    if (!Array.isArray(items) || items.length === 0) return FALLBACK_MASTERS
    return items.filter(item => item && item.name)
})

// Алиант для шаблона — только карточки с именем.
const masters = computed(() => displayMasters.value)

function experienceWord(n) {
    const nAbs = Math.abs(parseInt(n, 10)) || 0
    if (nAbs % 100 >= 11 && nAbs % 100 <= 14) return 'лет'
    const last = nAbs % 10
    if (last === 1) return 'год'
    if (last >= 2 && last <= 4) return 'года'
    return 'лет'
}
</script>

<style scoped>
.masters {
    max-width: 1120px;
    margin: 0 auto;
    padding: 56px 20px 80px;
}
.masters__heading {
    text-align: center;
    margin: 0 0 40px;
    font-size: 2rem;
    color: #111827;
}
.masters__blocks {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    justify-content: center;
}
.masters__block {
    position: relative;
    width: calc(calc(100% / 3) - calc(48px / 3));
    min-width: 260px;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    background: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.masters__block:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(17, 24, 39, 0.08);
    border-color: #d1d5db;
}
.masters__block-img {
    width: 100%;
    height: 260px;
    object-fit: cover;
    display: block;
}
.masters__block-placeholder {
    width: 100%;
    height: 260px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
    color: #fff;
    font-size: 3rem;
    font-weight: 700;
}
.masters__block-info {
    padding: 24px;
    text-align: center;
    width: 100%;
}
.masters__block-name {
    font-weight: 600;
    font-size: 1.1rem;
    margin: 0 0 4px 0;
    color: #111827;
}
.masters__block-specialty {
    color: #6b7280;
    font-size: 0.95rem;
    margin: 0 0 6px 0;
}
.masters__block-experience {
    color: #111827;
    font-size: 0.9rem;
    margin: 0;
}

@media (max-width: 900px) {
    .masters {
        padding: 40px 16px 56px;
    }
    .masters__block {
        width: calc(calc(100% / 2) - calc(24px / 2));
    }
    .masters__block-img,
    .masters__block-placeholder {
        height: 220px;
    }
}

@media (max-width: 640px) {
    .masters__block {
        width: 100%;
    }
    .masters__block-img,
    .masters__block-placeholder {
        height: 200px;
    }
}
</style>

