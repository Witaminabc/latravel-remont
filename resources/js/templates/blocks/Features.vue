<template>
    <section class="features">
        <div class="features__grid">
            <article v-for="(item, index) in items" :key="index" class="feature">
                <img v-if="item.logo" class="feature__logo" :src="item.logo" alt="" />
                <i v-else-if="item.icon" :class="['feature__icon', item.icon]"></i>

                <h3 v-if="item.title" class="feature__title">{{ item.title }}</h3>
                <p v-if="item.description" class="feature__text">{{ item.description }}</p>

                <span v-if="item.popular" class="feature__badge">Популярное</span>
            </article>
        </div>
    </section>
</template>

<script setup>
/**
 * Секция «Преимущества» (repeater features из группы group_home).
 */
defineProps({
    items: { type: Array, default: () => [] },
})
</script>

<style scoped>
.features {
    max-width: 1120px;
    margin: 0 auto;
    padding: 56px 20px 80px;
}

.features__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 24px;
}

.feature {
    position: relative;
    padding: 24px;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    background: #fff;
}

.feature__logo {
    max-height: 56px;
    margin-bottom: 12px;
}

.feature__icon {
    display: block;
    font-size: 1.8rem;
    margin-bottom: 12px;
}

.feature__title {
    margin: 0 0 8px;
    font-size: 1.15rem;
}

.feature__text {
    margin: 0;
    color: #4b5563;
    line-height: 1.6;
}

.feature__badge {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 2px 8px;
    border-radius: 999px;
    background: #ecfdf5;
    color: #047857;
    font-size: 0.75rem;
    font-weight: 600;
}

@media (max-width: 720px) {
    .features {
        padding: 40px 16px 56px;
    }
}
</style>