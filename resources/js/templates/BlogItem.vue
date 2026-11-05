<template>
    <div class="page page--blog-item">
        <section class="blog-item-hero">
            <a v-if="acf.item_link_back" class="blog-item__back" :href="acf.item_link_back">← Назад к блогу</a>
            <h1 class="blog-item__title">{{ acf.item_title || page.title }}</h1>
            <div class="blog-item__meta">
                <span v-if="acf.item_date" class="blog-item__date">{{ acf.item_date }}</span>
                <span v-if="acf.item_category" class="blog-item__category">{{ acf.item_category }}</span>
            </div>
        </section>

        <section v-if="acf.item_image" class="blog-item-image">
            <img
                :src="acf.item_image"
                :alt="acf.item_title"
                class="blog-item-image__img"
                loading="lazy"
            />
        </section>

        <section v-if="acf.item_content" class="blog-item-content">
            <div v-html="acf.item_content" class="blog-item-content__text"></div>
        </section>
    </div>
</template>

<script setup>
/**
 * Детальная страница статьи блога.
 * Контент приходит из ACF-группы group_blog_item (item_content и др.).
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
.page--blog-item {
    max-width: 800px;
    margin: 0 auto;
    padding: 56px 20px 80px;
}

.blog-item-hero {
    margin-bottom: 24px;
}

.blog-item__back {
    display: inline-block;
    margin-bottom: 12px;
    color: #2563eb;
    text-decoration: none;
    font-size: 0.95rem;
}

.blog-item__back:hover {
    text-decoration: underline;
}

.blog-item__title {
    margin: 0 0 12px;
    font-size: 2rem;
    line-height: 1.2;
    color: #111827;
}

.blog-item__meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 20px;
    color: #6b7280;
    font-size: 0.9rem;
}

.blog-item__date,
.blog-item__category {
    display: inline-block;
}

.blog-item-image {
    margin-bottom: 24px;
}

.blog-item-image__img {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 12px;
    background: #f3f4f6;
}

.blog-item-content__text {
    color: #374151;
    line-height: 1.75;
    font-size: 1.05rem;
}

.blog-item-content__text :deep(p) {
    margin: 0 0 1.2em;
}

.blog-item-content__text :deep(h2),
.blog-item-content__text :deep(h3) {
    color: #111827;
    margin: 1.6em 0 0.6em;
}

.blog-item-content__text :deep(ul),
.blog-item-content__text :deep(ol) {
    margin: 0 0 1.2em 1.5em;
    padding: 0;
}

.blog-item-content__text :deep(li) {
    margin-bottom: 0.4em;
}

.blog-item-content__text :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}
</style>