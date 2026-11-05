<template>
    <div class="site">
        <SiteHeader />
        <main class="site__main">
            <slot />
        </main>
        <SiteFooter />
    </div>
</template>

<script setup>
/**
 * Публичный layout: общая шапка на всех шаблонах страниц.
 */
import SiteHeader from './SiteHeader.vue'
import SiteFooter from './SiteFooter.vue'
</script>

<style>
body {
    margin: 0;
}

.site {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background: #fff;
    color: #111827;
}

.site__main {
    flex: 1 1 auto;
    min-width: 0;
}
</style>
