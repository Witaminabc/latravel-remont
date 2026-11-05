<template>
    <div class="flex align-items-center gap-2">
        <InputSwitch v-model="model" />
        <span class="text-500">{{ model ? (field.onLabel || 'Да') : (field.offLabel || 'Нет') }}</span>
    </div>
</template>

<script setup>
import InputSwitch from 'primevue/inputswitch'

defineProps({
    field: { type: Object, required: true },
})

const model = defineModel({ default: false })
</script>
