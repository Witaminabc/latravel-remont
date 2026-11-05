<template>
    <div class="page page--about">
        <template v-if="blocksOn">
            <!-- Hero: история компании -->
            <Hero
                :title="acf.about_heading || page.title"
                :subtitle="acf.about_subtitle"
                :text="acf.about_intro"
                :image="acf.about_image"
                :rating="acf.about_rating"
            />

            <!-- Цифры компании (репитер about_stats) -->
            <section v-if="stats.length" class="about-stats">
                <div class="about-stats__grid">
                    <article
                        v-for="(item, index) in stats"
                        :key="index"
                        class="about-stat"
                    >
                        <div class="about-stat__number">{{ item.number }}</div>
                        <div class="about-stat__label">{{ item.label }}</div>
                    </article>
                </div>
            </section>

            <!-- Стандарты качества (репитер about_standards) -->
            <section v-if="standards.length" class="about-standards">
                <h2 class="about-standards__heading">
                    {{ acf.about_standards_heading || 'Стандарты качества ремонтной компании' }}
                </h2>
                <div class="about-standards__grid">
                    <article
                        v-for="(item, index) in standards"
                        :key="index"
                        class="about-standard"
                    >
                        <div class="about-standard__badge">
                            {{ String(index + 1).padStart(2, '0') }}
                        </div>
                        <div class="about-standard__body">
                            <h3 class="about-standard__title">{{ item.title }}</h3>
                            <p class="about-standard__text">{{ item.description }}</p>
                            <p v-if="item.quote" class="about-standard__quote">
                                «{{ item.quote }}»
                            </p>
                        </div>
                    </article>
                </div>
            </section>

            <!-- Штат мастеров (блок Masters из group_home_masters / about_masters) -->
            <Masters
                v-if="masters.length"
                :heading="acf.about_masters_heading"
                :masters="masters"
            />
        </template>

        <section v-if="page.content" class="about-content">
            <div v-html="page.content"></div>
        </section>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import Hero from './blocks/Hero.vue'
import Masters from './blocks/Masters.vue'

const props = defineProps({
    page: { type: Object, required: true },
    acf: { type: Object, default: () => ({}) },
    seo: { type: Object, default: () => ({}) },
    settings: { type: Object, default: () => ({}) },
    template: { type: String, default: '' },
})

// Выключение блока в админке: meta.blocks_enabled.group_about (по умолчанию включён)
const blocksOn = computed(() => props.page?.meta?.blocks_enabled?.group_about !== false)

// Цифры компании (репитер about_stats)
const stats = computed(() => {
    const items = props.acf?.about_stats
    return Array.isArray(items) ? items : []
})

// Стандарты качества (репитер about_standards)
const standards = computed(() => {
    const items = props.acf?.about_standards
    return Array.isArray(items) ? items.filter(i => i && i.title) : []
})

// Мастера для страницы «О нас» (репитер about_masters)
const masters = computed(() => {
    const items = props.acf?.about_masters
    return Array.isArray(items) ? items.filter(i => i && i.name) : []
})
</script>

<style scoped>
.page--about {
    max-width: 1120px;
    margin: 0 auto;
    padding: 56px 20px 80px;
}

.about-stats {
    margin: 48px 0;
    padding: 40px 24px;
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    color: #fff;
    border-radius: 20px;
}

.about-stats__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 24px;
    max-width: 900px;
    margin: 0 auto;
}

.about-stat {
    text-align: center;
    padding: 16px;
}

.about-stat__number {
    font-size: 2.75rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 8px;
    display: block;
}

.about-stat__label {
    font-size: 1rem;
    opacity: 0.95;
    line-height: 1.5;
}

.about-standards {
    margin: 56px 0;
}

.about-standards__heading {
    text-align: center;
    font-size: 2rem;
    margin: 0 0 40px;
    color: #111827;
}

.about-standards__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

.about-standard {
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    padding: 24px;
    background: #fff;
    display: flex;
    gap: 16px;
}

.about-standard__badge {
    flex: 0 0 48px;
    height: 48px;
    border-radius: 12px;
    background: #fef3c7;
    color: #92400e;
    font-weight: 800;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.about-standard__body {
    flex: 1;
}

.about-standard__title {
    margin: 0 0 10px;
    font-size: 1.1rem;
    line-height: 1.4;
}

.about-standard__text {
    margin: 0 0 10px;
    color: #4b5563;
    font-size: 0.95rem;
    line-height: 1.6;
}

.about-standard__quote {
    margin: 0;
    color: #111827;
    font-size: 0.9rem;
    font-style: italic;
    line-height: 1.5;
}

.about-content {
    margin-top: 40px;
    font-size: 1.05rem;
    line-height: 1.7;
    color: #374151;
}

.about-content :deep(img) {
    max-width: 100%;
    height: auto;
}

@media (max-width: 760px) {
    .about-standard {
        flex-direction: column;
    }
    .about-standard__badge {
        align-self: flex-start;
    }
    .about-stat__number {
        font-size: 2rem;
    }
}
</style>
