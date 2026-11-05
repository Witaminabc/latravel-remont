<template>
    <Textarea v-model="model" class="w-full" :rows="field.rows || 4" :placeholder="field.placeholder || ''" />
</template>

<script setup>
import Textarea from 'primevue/textarea'

defineProps({
    field: { type: Object, required: true },
})

const model = defineModel({ default: '' })
</script>
