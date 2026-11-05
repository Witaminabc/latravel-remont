<template>
    <div class="rich-text" v-html="html"></div>
</template>

<script setup>
/**
 * Секция «RichText»: вывод произвольного HTML (Wysiwyg-поле или content).
 * HTML очищается на сервере (Санитизация, Фаза 8).
 */
defineProps({
    html: { type: String, default: '' },
})
</script>

<style scoped>
.rich-text {
    font-size: 1.05rem;
    line-height: 1.7;
}

.rich-text :deep(img) {
    max-width: 100%;
    height: auto;
}

.rich-text :deep(figure) {
    margin: 20px 0;
}

.rich-text :deep(blockquote) {
    margin: 20px 0;
    padding: 8px 16px;
    border-left: 4px solid #d1d5db;
    color: #4b5563;
}
</style>