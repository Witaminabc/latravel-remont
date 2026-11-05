<template>
    <div v-if="group && group.fields && group.fields.length" class="acf-group">
        <div v-if="group.title" class="acf-group__title">{{ group.title }}</div>

        <div v-for="field in group.fields" :key="field.key || field.name" class="acf-field">
            <label class="acf-label">
                {{ field.label || field.name }}
                <span v-if="field.required" class="acf-required">*</span>
            </label>
            <small v-if="field.instructions" class="acf-instructions">{{ field.instructions }}</small>

            <AcfField
                :field="field"
                :model-value="values[field.name]"
                @update:model-value="(val) => setValue(field.name, val)"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import AcfField from './AcfField.vue'

const props = defineProps({
    group: { type: Object, required: true },
})

const model = defineModel({ type: Object, default: () => ({}) })

const values = computed(() => model.value ?? {})

function setValue(name, value) {
    model.value = { ...(model.value ?? {}), [name]: value }
}
</script>
