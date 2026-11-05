<template>
    <div class="acf-repeater">
        <div v-if="rows.length" class="acf-repeater__toolbar">
            <span class="acf-repeater__count">{{ rows.length }} стр.</span>
            <div class="acf-repeater__toolbar-actions">
                <button type="button" class="acf-repeater__toolbar-btn" @click="expandAll">Развернуть все</button>
                <button type="button" class="acf-repeater__toolbar-btn" @click="collapseAll">Свернуть все</button>
            </div>
        </div>

        <div class="acf-repeater__rows">
            <div v-for="(row, index) in rows" :key="index" class="acf-repeater__row" :class="{ 'is-open': !isCollapsed(index) }">
                <div class="acf-repeater__row-head" role="button" :tabindex="0"
                    @click="toggle(index)"
                    @keydown.enter.prevent="toggle(index)"
                    @keydown.space.prevent="toggle(index)">
                    <div class="acf-repeater__row-title">
                        <span class="acf-repeater__chevron" :class="{ 'is-open': !isCollapsed(index) }">▸</span>
                        <span class="acf-repeater__row-index">#{{ index + 1 }}</span>
                        <span v-if="isCollapsed(index)" class="acf-repeater__summary">{{ summary(row) }}</span>
                    </div>
                    <div class="acf-repeater__row-actions" @click.stop>
                        <button type="button" :disabled="index === 0" @click="moveUp(index)">↑</button>
                        <button type="button" :disabled="index === rows.length - 1" @click="moveDown(index)">↓</button>
                        <button type="button" class="danger" @click="remove(index)">✕</button>
                    </div>
                </div>

                <div v-show="!isCollapsed(index)" class="acf-repeater__body">
                    <div v-for="sub in subFields" :key="sub.name" class="acf-field">
                        <label class="acf-label">{{ sub.label || sub.name }}</label>
                        <AcfField
                            :field="sub"
                            :model-value="row[sub.name]"
                            @update:model-value="(val) => setSub(index, sub.name, val)"
                        />
                    </div>
                </div>
            </div>
        </div>

        <button type="button" class="acf-repeater__add" :disabled="atMax" @click="add">+ Добавить</button>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import AcfField from '../AcfField.vue'

const props = defineProps({
    field: { type: Object, required: true },
})

const model = defineModel({ type: Array, default: () => [] })

const subFields = computed(() => props.field.sub_fields ?? [])
const rows = computed(() => (Array.isArray(model.value) ? model.value : []))
const atMax = computed(() => {
    const max = props.field.max
    return typeof max === 'number' && max > 0 && rows.value.length >= max
})

// Сворачивание строк репитера по индексу
const collapsed = ref({})

function isCollapsed(index) {
    return collapsed.value[index] === true
}

function toggle(index) {
    collapsed.value = { ...collapsed.value, [index]: !isCollapsed(index) }
}

function expandAll() {
    collapsed.value = {}
}

function collapseAll() {
    const all = {}
    rows.value.forEach((_, i) => { all[i] = true })
    collapsed.value = all
}

function stripHtml(value) {
    return String(value ?? '').replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim()
}

function summary(row) {
    for (const sub of subFields.value) {
        const value = row[sub.name]
        if (sub.type === 'repeater') {
            if (Array.isArray(value) && value.length) return value.length + ' подпункт(ов)'
        } else if (sub.type === 'boolean') {
            if (value === true) return 'Да'
        } else if (Array.isArray(value)) {
            if (value.length) return value.join(', ')
        } else {
            const text = stripHtml(value)
            if (text) return text.length > 60 ? text.slice(0, 60) + '…' : text
        }
    }
    return 'Пусто'
}

function emptyRow() {
    const row = {}
    for (const sub of subFields.value) {
        if (sub.type === 'repeater') row[sub.name] = []
        else if (sub.type === 'boolean') row[sub.name] = false
        else if (sub.type === 'select' && sub.multiple) row[sub.name] = []
        else row[sub.name] = ''
    }
    return row
}

function add() {
    model.value = [...rows.value, emptyRow()]
}

function remove(index) {
    model.value = rows.value.filter((_, i) => i !== index)
    collapsed.value = {}
}

function moveUp(index) {
    if (index === 0) return
    const a = [...rows.value]
    ;[a[index - 1], a[index]] = [a[index], a[index - 1]]
    model.value = a
    collapsed.value = {}
}

function moveDown(index) {
    if (index === rows.value.length - 1) return
    const a = [...rows.value]
    ;[a[index], a[index + 1]] = [a[index + 1], a[index]]
    model.value = a
    collapsed.value = {}
}

function setSub(index, name, value) {
    model.value = rows.value.map((row, i) => (i === index ? { ...row, [name]: value } : row))
}
</script>
