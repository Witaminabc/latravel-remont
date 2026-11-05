<template>
    <router-view />
</template>

<script setup>
import { onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import { getCsrfToken } from './services/api'

const toast = useToast()

onMounted(async () => {
    try {
        await getCsrfToken()
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Ошибка',
            detail: 'Не удалось подключиться к серверу',
            life: 3000
        })
    }
})
</script>

<style>
/*@import 'primevue/resources/themes/lara-light-blue/theme.css';*/
/*@import 'primevue/resources/primevue.css';*/
/*@import 'primeicons/primeicons.css';*/
/*@import 'primeflex/primeflex.css';*/

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    background: #f1f5f9;
}

#app {
    min-height: 100vh;
}
</style>
