<template>
    <Editor v-model="model" editorStyle="height: 220px" />
</template>

<script setup>
import Editor from 'primevue/editor'

defineProps({
    field: { type: Object, required: true },
})

const model = defineModel({ default: '' })
</script>
