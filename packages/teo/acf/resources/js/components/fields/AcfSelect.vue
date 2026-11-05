<template>
    <MultiSelect
        v-if="isMultiple"
        v-model="model"
        :options="options"
        option-label="label"
        option-value="value"
        display="chip"
        class="w-full"
        :placeholder="field.placeholder || ''"
    />
    <Dropdown
        v-else
        v-model="model"
        :options="options"
        option-label="label"
        option-value="value"
        class="w-full"
        :show-clear="!field.required"
        :placeholder="field.placeholder || ''"
    />
</template>

<script setup>
import { computed } from 'vue'
import Dropdown from 'primevue/dropdown'
import MultiSelect from 'primevue/multiselect'

const props = defineProps({
    field: { type: Object, required: true },
})

const model = defineModel({ default: null })

const isMultiple = computed(() => !!props.field.multiple)
const options = computed(() =>
    (props.field.options ?? []).map((o) =>
        o && typeof o === 'object' ? o : { label: String(o), value: o }
    )
)
</script>
