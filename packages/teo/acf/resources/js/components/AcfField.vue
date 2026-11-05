<template>
    <component
        :is="componentFor(field.type)"
        :field="field"
        :model-value="modelValue"
        @update:model-value="(val) => emit('update:modelValue', val)"
    />
</template>

<script setup>
import AcfText from './fields/AcfText.vue'
import AcfTextarea from './fields/AcfTextarea.vue'
import AcfWysiwyg from './fields/AcfWysiwyg.vue'
import AcfNumber from './fields/AcfNumber.vue'
import AcfSelect from './fields/AcfSelect.vue'
import AcfBoolean from './fields/AcfBoolean.vue'
import AcfImage from './fields/AcfImage.vue'
import AcfRepeater from './fields/AcfRepeater.vue'

defineProps({
    field: { type: Object, required: true },
    modelValue: { default: null },
})

const emit = defineEmits(['update:modelValue'])

const map = {
    text: AcfText,
    textarea: AcfTextarea,
    wysiwyg: AcfWysiwyg,
    number: AcfNumber,
    select: AcfSelect,
    boolean: AcfBoolean,
    image: AcfImage,
    repeater: AcfRepeater,
}

function componentFor(type) {
    return map[type] ?? AcfText
}
</script>
