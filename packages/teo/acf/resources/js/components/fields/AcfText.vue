<template>
    <InputText v-model="model" class="w-full" :placeholder="field.placeholder || ''" />
</template>

<script setup>
import InputText from 'primevue/inputtext'

defineProps({
    field: { type: Object, required: true },
})

const model = defineModel({ default: '' })
</script>
