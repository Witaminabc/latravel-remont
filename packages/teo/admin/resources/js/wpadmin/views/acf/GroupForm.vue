<template>
    <div class="acf-group-form">
        <div class="flex justify-content-between align-items-center mb-4">
            <div class="flex align-items-center gap-3">
                <Button icon="pi pi-arrow-left" class="p-button-text" @click="back" />
                <div>
                    <h1 class="text-2xl font-bold mb-1">
                        {{ isEdit ? 'Редактирование группы' : 'Новая группа полей' }}
                    </h1>
                    <p class="text-color-secondary m-0">
                        {{ isEdit ? `Ключ: ${form.key}` : 'Ключ будет сгенерирован автоматически' }}
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <Button label="Отмена" class="p-button-text" @click="back" />
                <Button label="Сохранить" icon="pi pi-check" :loading="saving" @click="save" />
            </div>
        </div>

        <div class="grid">
            <div class="col-12 lg:col-8">
                <Card class="mb-4">
                    <template #title>Группа</template>
                    <template #content>
                        <div class="mb-3">
                            <label class="block mb-1">Название</label>
                            <InputText v-model="form.title" class="w-full" placeholder="Настройки главной страницы" />
                        </div>

                        <div class="mb-1">
                            <label class="block mb-1">Ключ</label>
                            <InputText
                                v-model="form.key"
                                class="w-full"
                                :disabled="isEdit"
                                placeholder="group_home (можно оставить пустым)"
                            />
                            <small class="text-color-secondary block mt-1">
                                {{ isEdit
                                    ? 'Ключ группы неизменяем — на нём могут ссылаться данные.'
                                    : 'Оставьте пустым — будет сгенерирован автоматически.' }}
                            </small>
                        </div>
                    </template>
                </Card>

                <Card class="mb-4">
                    <template #title>Условия отображения</template>
                    <template #content>
                        <div
                            v-for="(rule, index) in form.location"
                            :key="index"
                            class="flex gap-2 align-items-center mb-2"
                        >
                            <Dropdown
                                v-model="rule.type"
                                :options="locationTypes"
                                optionLabel="label"
                                optionValue="value"
                                class="acf-location__type"
                            />
                            <InputText
                                v-model="rule.value"
                                class="flex-1"
                                :placeholder="rule.type === 'slug' ? 'home' : 'page'"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-text p-button-danger"
                                @click="removeLocation(index)"
                            />
                        </div>

                        <div class="flex justify-content-between align-items-center mt-3">
                            <small class="text-color-secondary">
                                Все условия должны выполняться одновременно (логическое «И»).
                            </small>
                            <Button
                                label="Добавить условие"
                                icon="pi pi-plus"
                                class="p-button-text p-button-sm"
                                @click="addLocation"
                            />
                        </div>
                    </template>
                </Card>
                <Card>
                    <template #title>Поля</template>
                    <template #content>
                        <div v-if="form.fields.length">
                            <div v-for="(field, index) in form.fields" :key="field.key || index" class="acf-field-row">
                                <div class="flex align-items-center gap-2 flex-1" style="min-width: 0">
                                    <strong class="acf-field-row__label">{{ field.label || field.name }}</strong>
                                    <code>{{ field.name }}</code>
                                    <Tag :value="typeLabel(field.type)" severity="info" />
                                    <Tag
                                        v-if="field.type === 'repeater'"
                                        :value="(field.sub_fields || []).length + ' подполей'"
                                        severity="warning"
                                    />
                                    <span
                                        v-if="field.required"
                                        class="acf-field-row__required"
                                        title="Обязательное"
                                    >*</span>
                                </div>
                                <div class="flex gap-1">
                                    <Button
                                        icon="pi pi-arrow-up"
                                        class="p-button-text p-button-sm"
                                        :disabled="index === 0"
                                        @click="moveField(index, -1)"
                                    />
                                    <Button
                                        icon="pi pi-arrow-down"
                                        class="p-button-text p-button-sm"
                                        :disabled="index === form.fields.length - 1"
                                        @click="moveField(index, 1)"
                                    />
                                    <Button icon="pi pi-pencil" class="p-button-text p-button-sm" @click="openField(index)" />
                                    <Button
                                        icon="pi pi-trash"
                                        class="p-button-text p-button-danger p-button-sm"
                                        @click="removeField(index)"
                                    />
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-color-secondary py-3 text-center">
                            Поля не добавлены. Группа без полей не отображается в формах.
                        </div>

                        <Button label="Добавить поле" icon="pi pi-plus" class="mt-3" @click="openField(-1)" />
                    </template>
                </Card>
            </div>

            <div class="col-12 lg:col-4">
                <Card>
                    <template #title>Как это работает</template>
                    <template #content>
                        <ul class="acf-group-form__hints m-0 pl-3">
                            <li class="mb-2">
                                Группа отображается в форме редактирования материала, если выполняются все её условия.
                            </li>
                            <li class="mb-2">
                                Значения вводятся в карточке «Кастомные поля» на странице/записи.
                            </li>
                            <li class="mb-2">
                                Имя поля (machine name) — ключ значения: не меняйте его после появления данных.
                            </li>
                            <li class="mb-2">
                                Группы со статусом «Код» зарегистрированы в приложении; правка сохранит
                                переопределение в БД, удаление скроет группу.
                            </li>
                        </ul>
                    </template>
                </Card>
            </div>
        </div>

        <FieldEditor
            v-model:visible="fieldDialogVisible"
            :field="editingField"
            :types="types"
            @save="applyField"
        />
    </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import Card from 'primevue/card'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Tag from 'primevue/tag'
import api from '../../services/api'
import FieldEditor from './FieldEditor.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const isEdit = computed(() => route.name === 'acf-groups.edit')
const groupKey = computed(() => route.params.key)

const saving = ref(false)
const types = ref([])

const form = ref({
    key: '',
    title: '',
    location: [{ type: 'post_type', value: 'page' }],
    fields: [],
})

const locationTypes = [
    { label: 'Тип материала', value: 'post_type' },
    { label: 'Слаг', value: 'slug' },
]

const fieldDialogVisible = ref(false)
const editingIndex = ref(-1)
const editingField = ref(null)

const typeLabel = (value) => {
    const item = types.value.find((t) => t.value === value)
    return item ? item.label : value
}

const fetchTypes = async () => {
    try {
        const { data } = await api.get('/admin/acf/types')
        types.value = data.data ?? data ?? []
    } catch (error) {
        console.error(error)
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось загрузить типы полей', life: 3000 })
    }
}

const fetchGroup = async () => {
    if (!isEdit.value) return

    try {
        const { data } = await api.get(`/admin/acf/groups/${groupKey.value}`)
        const group = data.data ?? data

        form.value = {
            key: group.key,
            title: group.title,
            location: (group.location ?? []).map((rule) => ({ ...rule })),
            fields: group.fields ?? [],
        }
    } catch (error) {
        console.error(error)
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось загрузить группу', life: 3000 })
        router.push({ name: 'acf-groups.index' })
    }
}

const addLocation = () => form.value.location.push({ type: 'post_type', value: '' })
const removeLocation = (index) => form.value.location.splice(index, 1)

const openField = (index) => {
    editingIndex.value = index
    editingField.value =
        index === -1
            ? { label: '', name: '', type: 'text', instructions: '', required: false, placeholder: '' }
            : JSON.parse(JSON.stringify(form.value.fields[index]))

    fieldDialogVisible.value = true
}

const applyField = (field) => {
    if (editingIndex.value === -1) {
        form.value.fields.push(field)
    } else {
        form.value.fields.splice(editingIndex.value, 1, field)
    }

    fieldDialogVisible.value = false
}
const removeField = (index) => {
    const field = form.value.fields[index]
    if (!confirm(`Удалить поле «${field.label || field.name}»?`)) return

    form.value.fields.splice(index, 1)
}

const moveField = (index, delta) => {
    const target = index + delta
    const fields = form.value.fields

    if (target < 0 || target >= fields.length) return

    const moved = fields.splice(index, 1)[0]
    fields.splice(target, 0, moved)
}

const save = async () => {
    if (!form.value.title.trim()) {
        toast.add({ severity: 'warn', summary: 'Внимание', detail: 'Укажите название группы', life: 3000 })
        return
    }

    if (!form.value.location.length) {
        toast.add({ severity: 'warn', summary: 'Внимание', detail: 'Добавьте условие отображения', life: 3000 })
        return
    }

    saving.value = true

    try {
        const payload = {
            title: form.value.title,
            location: form.value.location,
            fields: form.value.fields,
        }

        if (isEdit.value) {
            await api.put(`/admin/acf/groups/${groupKey.value}`, payload)
        } else {
            payload.key = form.value.key || undefined
            await api.post('/admin/acf/groups', payload)
        }

        toast.add({ severity: 'success', summary: 'Успех', detail: 'Группа сохранена', life: 3000 })
        router.push({ name: 'acf-groups.index' })
    } catch (error) {
        console.error(error)

        const data = error.response?.data
        const errors = data?.errors ? Object.values(data.errors).flat() : []

        toast.add({
            severity: 'error',
            summary: 'Ошибка',
            detail: errors[0] || data?.message || 'Не удалось сохранить группу',
            life: 5000,
        })
    } finally {
        saving.value = false
    }
}

const back = () => router.push({ name: 'acf-groups.index' })

onMounted(async () => {
    await Promise.all([fetchTypes(), fetchGroup()])
})
</script>

<style scoped>
.acf-location__type {
    width: 200px;
    flex: 0 0 200px;
}

.acf-field-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 8px 10px;
    border: 1px solid var(--surface-200);
    border-radius: 4px;
    margin-bottom: 8px;
    background: var(--surface-50);
}

.acf-field-row__label {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.acf-field-row__required {
    color: #e5484d;
    font-weight: 700;
}

.acf-group-form__hints {
    list-style: disc;
}
</style>