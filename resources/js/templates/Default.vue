<template>
    <div class="page page--default">
        <header class="page__header">
            <h1>{{ page.title }}</h1>
        </header>

        <article v-if="page.content" class="page__content" v-html="page.content"></article>
    </div>
</template>

<script setup>
/**
 * Стандартный шаблон страницы: заголовок и контент.
 * Состав секций фиксирован, данные приходят пропсами Inertia.
 */
defineProps({
    page: { type: Object, required: true },
    acf: { type: Object, default: () => ({}) },
    seo: { type: Object, default: () => ({}) },
    settings: { type: Object, default: () => ({}) },
    template: { type: String, default: '' },
})
</script>

<style scoped>
.page--default {
    max-width: 960px;
    margin: 0 auto;
    padding: 56px 20px 80px;
}

.page__header h1 {
    margin: 0 0 24px;
    font-size: 2rem;
    line-height: 1.2;
    color: #111827;
}

.page__content {
    font-size: 1.05rem;
    line-height: 1.7;
}

.page__content :deep(img) {
    max-width: 100%;
    height: auto;
}
</style>