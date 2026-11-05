<template>
    <Dialog
        :visible="visible"
        :header="header"
        :modal="true"
        :style="{ width: '680px' }"
        @update:visible="emit('update:visible', $event)"
    >
        <div v-if="current" class="field-editor">
            <div class="grid">
                <div class="col-12 md:col-6 mb-3">
                    <label class="block mb-1">Название</label>
                    <InputText v-model="current.label" class="w-full" placeholder="Заголовок Hero-блока" />
                </div>
                <div class="col-12 md:col-6 mb-3">
                    <label class="block mb-1">Имя (machine name)</label>
                    <InputText
                        v-model="current.name"
                        class="w-full"
                        :class="{ 'p-invalid': nameError }"
                        placeholder="hero_title"
                    />
                    <small v-if="nameError" class="p-error block mt-1">{{ nameError }}</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="block mb-1">Тип</label>
                <Dropdown
                    v-model="current.type"
                    :options="availableTypes"
                    optionLabel="label"
                    optionValue="value"
                    class="w-full"
                />
            </div>

            <div class="mb-3">
                <label class="block mb-1">Инструкция</label>
                <Textarea v-model="current.instructions" :rows="2" class="w-full" placeholder="Подсказка для редактора" />
            </div>

            <div class="flex align-items-center gap-2 mb-3">
                <Checkbox v-model="current.required" :binary="true" inputId="acf-field-required" />
                <label for="acf-field-required">Обязательное поле</label>
            </div>

            <div v-if="current.type !== 'boolean'" class="mb-3">
                <label class="block mb-1">Placeholder</label>
                <InputText v-model="current.placeholder" class="w-full" />
            </div>

            <div v-if="current.type === 'textarea'" class="mb-3">
                <label class="block mb-1">Количество строк</label>
                <InputNumber v-model="current.rows" :min="1" :max="30" showButtons class="w-full" />
            </div>
            <div v-if="current.type === 'number'" class="grid">
                <div class="col-12 md:col-4 mb-3">
                    <label class="block mb-1">Минимум</label>
                    <InputNumber v-model="current.min" showButtons class="w-full" />
                </div>
                <div class="col-12 md:col-4 mb-3">
                    <label class="block mb-1">Максимум</label>
                    <InputNumber v-model="current.max" showButtons class="w-full" />
                </div>
                <div class="col-12 md:col-4 mb-3">
                    <label class="block mb-1">По умолчанию</label>
                    <InputNumber v-model="current.default" showButtons class="w-full" />
                </div>
            </div>

            <div v-if="current.type === 'select'" class="mb-3">
                <div class="flex justify-content-between align-items-center mb-2">
                    <label class="font-semibold">Варианты</label>
                    <Button label="Добавить" icon="pi pi-plus" class="p-button-text p-button-sm" @click="addOption" />
                </div>
                <div v-for="(option, index) in current.options" :key="index" class="flex gap-2 mb-2 align-items-center">
                    <InputText v-model="option.label" class="flex-1" placeholder="Название" />
                    <InputText v-model="option.value" class="flex-1" placeholder="Значение" />
                    <Button
                        icon="pi pi-trash"
                        class="p-button-text p-button-danger"
                        @click="removeOption(index)"
                    />
                </div>
                <div class="flex align-items-center gap-2 mt-2">
                    <Checkbox v-model="current.multiple" :binary="true" inputId="acf-field-multiple" />
                    <label for="acf-field-multiple">Множественный выбор</label>
                </div>
            </div>

            <div v-if="current.type === 'boolean'" class="grid">
                <div class="col-12 md:col-4 mb-3">
                    <label class="block mb-1">Метка «вкл»</label>
                    <InputText v-model="current.onLabel" class="w-full" />
                </div>
                <div class="col-12 md:col-4 mb-3">
                    <label class="block mb-1">Метка «выкл»</label>
                    <InputText v-model="current.offLabel" class="w-full" />
                </div>
                <div class="col-12 md:col-4 mb-3 flex align-items-center gap-2">
                    <Checkbox v-model="current.default" :binary="true" inputId="acf-field-default" />
                    <label for="acf-field-default">Включено по умолчанию</label>
                </div>
            </div>

            <div v-if="current.type === 'image'" class="mb-3 text-color-secondary">
                <small>Изображение выбирается из медиатеки; значение хранится как URL.</small>
            </div>
            <!-- Репитер: подполя (только для поля верхнего уровня) -->
            <div v-if="mode === 'field' && current.type === 'repeater'" class="acf-subfields mb-3">
                <div class="grid mb-2">
                    <div class="col-12 md:col-4">
                        <label class="block mb-1">Строк минимум</label>
                        <InputNumber v-model="current.min" :min="0" showButtons class="w-full" />
                    </div>
                    <div class="col-12 md:col-4">
                        <label class="block mb-1">Строк максимум</label>
                        <InputNumber v-model="current.max" :min="0" showButtons class="w-full" />
                    </div>
                    <div class="col-12 md:col-4">
                        <label class="block mb-1">Вёрстка</label>
                        <Dropdown
                            v-model="current.layout"
                            :options="layoutOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>
                </div>

                <div class="flex justify-content-between align-items-center mb-2">
                    <label class="font-semibold">Подполя</label>
                    <Button
                        label="Добавить подполе"
                        icon="pi pi-plus"
                        class="p-button-text p-button-sm"
                        @click="addSub"
                    />
                </div>

                <div v-for="(sub, index) in current.sub_fields" :key="sub.key || index" class="acf-subfield-row">
                    <div class="flex align-items-center gap-2 flex-1" style="min-width: 0">
                        <strong class="acf-subfield-row__label">{{ sub.label || sub.name }}</strong>
                        <code>{{ sub.name }}</code>
                        <Tag :value="typeLabel(sub.type)" severity="info" />
                    </div>
                    <div class="flex gap-1">
                        <Button icon="pi pi-pencil" class="p-button-text p-button-sm" @click="editSub(index)" />
                        <Button
                            icon="pi pi-trash"
                            class="p-button-text p-button-danger p-button-sm"
                            @click="removeSub(index)"
                        />
                    </div>
                </div>

                <small v-if="!(current.sub_fields && current.sub_fields.length)" class="text-color-secondary block mt-1">
                    Подполя не добавлены — репитер не будет отображаться в форме.
                </small>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-content-between align-items-center w-full">
                <Button
                    v-if="mode === 'sub'"
                    label="Назад"
                    icon="pi pi-arrow-left"
                    class="p-button-text"
                    @click="cancelSub"
                />
                <Button v-else label="Отмена" icon="pi pi-times" class="p-button-text" @click="close" />

                <Button
                    v-if="mode === 'sub'"
                    label="Применить"
                    icon="pi pi-check"
                    :disabled="!!nameError"
                    @click="applySub"
                />
                <Button
                    v-else
                    label="Сохранить"
                    icon="pi pi-check"
                    :disabled="!!nameError"
                    @click="save"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import Checkbox from 'primevue/checkbox'
import InputNumber from 'primevue/inputnumber'
import Tag from 'primevue/tag'

const props = defineProps({
    visible: { type: Boolean, default: false },
    field: { type: Object, default: null },
    types: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:visible', 'save'])

const mode = ref('field')
const draft = ref(null)
const subDraft = ref(null)
const subIndex = ref(-1)

const layoutOptions = [
    { label: 'Блоками', value: 'block' },
    { label: 'Строкой', value: 'row' },
]

const current = computed(() => (mode.value === 'sub' ? subDraft.value : draft.value))

const header = computed(() => {
    if (mode.value === 'sub') {
        return subIndex.value === -1 ? 'Новое подполе' : 'Редактирование подполя'
    }
    return props.field && props.field.key ? 'Редактирование поля' : 'Новое поле'
})

const availableTypes = computed(() =>
    mode.value === 'sub' ? props.types.filter((t) => t.value !== 'repeater') : props.types
)

const nameError = computed(() => {
    const field = current.value
    if (!field) return ''

    const name = String(field.name || '').trim()
    if (!name) return 'Укажите машинное имя поля'
    if (!/^[a-z][a-z0-9_]*$/.test(name)) {
        return 'Латиница в нижнем регистре, цифры и «_», начало с буквы'
    }

    return ''
})

watch(
    () => props.visible,
    (visible) => {
        if (!visible) return

        draft.value = clone(props.field)
        ensureShape(draft.value)
        subDraft.value = null
        subIndex.value = -1
        mode.value = 'field'
    }
)

watch(
    () => current.value && current.value.type,
    () => {
        if (current.value) ensureShape(current.value)
    }
)

function clone(value) {
    return JSON.parse(JSON.stringify(value ?? {}))
}

function ensureShape(field) {
    if (!field || typeof field !== 'object') return

    if (field.type === 'repeater' && !Array.isArray(field.sub_fields)) field.sub_fields = []
    if (field.type === 'select' && !Array.isArray(field.options)) field.options = []
    if (field.type === 'boolean') {
        if (field.onLabel === undefined) field.onLabel = 'Вкл'
        if (field.offLabel === undefined) field.offLabel = 'Выкл'
        if (field.default === undefined) field.default = false
    }
}

function typeLabel(value) {
    const item = props.types.find((t) => t.value === value)
    return item ? item.label : value
}

function addOption() {
    if (!Array.isArray(current.value.options)) current.value.options = []
    current.value.options.push({ label: '', value: '' })
}

function removeOption(index) {
    current.value.options.splice(index, 1)
}

function addSub() {
    subIndex.value = -1
    subDraft.value = {
        label: '',
        name: '',
        type: 'text',
        instructions: '',
        required: false,
        placeholder: '',
    }
    mode.value = 'sub'
}

function editSub(index) {
    subIndex.value = index
    subDraft.value = clone(draft.value.sub_fields[index])
    ensureShape(subDraft.value)
    mode.value = 'sub'
}

function removeSub(index) {
    draft.value.sub_fields.splice(index, 1)
}

function cancelSub() {
    mode.value = 'field'
    subDraft.value = null
    subIndex.value = -1
}

function applySub() {
    if (nameError.value) return

    if (!Array.isArray(draft.value.sub_fields)) draft.value.sub_fields = []

    if (subIndex.value === -1) {
        draft.value.sub_fields.push(subDraft.value)
    } else {
        draft.value.sub_fields.splice(subIndex.value, 1, subDraft.value)
    }

    cancelSub()
}

function save() {
    if (nameError.value) return
    emit('save', clone(draft.value))
}

function close() {
    emit('update:visible', false)
}
</script>

<style scoped>
.acf-subfields {
    border-top: 1px solid var(--surface-200);
    padding-top: 12px;
}

.acf-subfield-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 6px 8px;
    border: 1px solid var(--surface-200);
    border-radius: 4px;
    margin-bottom: 6px;
    background: var(--surface-50);
}

.acf-subfield-row__label {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>

