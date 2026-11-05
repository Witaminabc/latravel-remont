<template>
    <InputNumber v-model="model" class="w-full" :min="field.min" :max="field.max" :placeholder="field.placeholder || ''" />
</template>

<script setup>
import InputNumber from 'primevue/inputnumber'

defineProps({
    field: { type: Object, required: true },
})

const model = defineModel({ default: null })
</script>
