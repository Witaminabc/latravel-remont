<template>
    <section class="hero" :class="{ 'hero--with-image': image }">
        <div class="hero__body">
            <p v-if="rating !== null && rating !== undefined && rating !== ''" class="hero__rating">
                ★ {{ rating }}
            </p>

            <h1 class="hero__title">{{ title }}</h1>
            <p v-if="subtitle" class="hero__subtitle">{{ subtitle }}</p>
            <div v-if="text" class="hero__text" v-html="text"></div>
        </div>

        <div v-if="image" class="hero__media">
            <img :src="image" :alt="title" />
        </div>
    </section>
</template>

<script setup>
/**
 * Hero-секция главной страницы (группа group_home).
 */
defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    text: { type: String, default: '' },
    image: { type: String, default: null },
    rating: { type: [Number, String], default: null },
})
</script>

<style scoped>
.hero {
    display: flex;
    flex-wrap: wrap;
    gap: 32px;
    align-items: center;
    justify-content: space-between;
    max-width: 1120px;
    margin: 0 auto;
    padding: 56px 20px 80px;
}

.hero__body {
    flex: 1 1 320px;
}

.hero__rating {
    display: inline-block;
    margin: 0 0 12px;
    padding: 4px 10px;
    border-radius: 999px;
    background: #fef3c7;
    color: #92400e;
    font-weight: 600;
    font-size: 0.9rem;
}

.hero__title {
    margin: 0 0 12px;
    font-size: 2.6rem;
    line-height: 1.15;
}

.hero__subtitle {
    margin: 0 0 16px;
    font-size: 1.25rem;
    color: #4b5563;
}

.hero__text {
    font-size: 1.05rem;
    line-height: 1.7;
    color: #374151;
}

.hero__media {
    flex: 0 1 480px;
}

.hero__media img {
    width: 100%;
    border-radius: 16px;
    display: block;
}

@media (max-width: 720px) {
    .hero {
        padding: 40px 16px 56px;
    }

    .hero__title {
        font-size: 2rem;
    }
}
</style>