<template>
    <div class="pages-form">
        <div class="flex justify-content-between align-items-center mb-4">
            <div class="flex align-items-center gap-3">
                <h1 class="text-2xl font-bold">{{ isEdit ? `Редактирование ${typeText}` : `Новая ${typeText}` }}</h1>
                <Tag :severity="getTypeSeverity" :value="typeText" />
            </div>
            <div class="flex gap-2">
                <Button label="Назад" icon="pi pi-arrow-left" class="p-button-text" @click="goBack" />
                <Button label="Предпросмотр" icon="pi pi-eye" class="p-button-secondary" @click="preview" />
                <Button v-if="isEdit" label="Ссылка-черновик" icon="pi pi-link" class="p-button-secondary" @click="copyDraftLink" />
                <Button label="Сохранить" icon="pi pi-save" class="p-button-success" @click="savePage" />
                <Button v-if="isEdit && form.status !== 'published'" label="Опубликовать" icon="pi pi-check" class="p-button-primary" @click="publish" />
                <Button v-if="isEdit" label="Копировать" icon="pi pi-copy" class="p-button-secondary" @click="duplicate" />
                <Button v-if="isEdit" label="Удалить" icon="pi pi-trash" class="p-button-danger" @click="deletePage" />
            </div>
        </div>

        <!-- Информационная панель для страниц/записей -->
        <Message v-if="isEdit" severity="info" class="mb-4">
            <div class="flex justify-content-between align-items-center">
                <div>
                    <strong>ID:</strong> {{ form.id }} |
                    <strong>Создано:</strong> {{ formatDate(form.created_at) }} |
                    <strong>Автор:</strong> {{ form.author?.name || 'Неизвестен' }}
                    <span v-if="form.deleted_at" class="ml-3 text-red-500">
                        <i class="pi pi-trash"></i> Удалено: {{ formatDate(form.deleted_at) }}
                    </span>
                </div>
                <div v-if="form.deleted_at">
                    <Button label="Восстановить" icon="pi pi-refresh" size="small" @click="restorePage" />
                </div>
            </div>
        </Message>

        <div class="grid">
            <!-- Основная колонка -->
            <div class="col-12 lg:col-8">
                <Card>
                    <template #content>
                        <!-- Выбор типа -->
                        <div class="field mb-4">
                            <label class="font-bold block mb-2 required">Тип контента</label>
                            <div class="flex gap-3">
                                <div class="flex align-items-center">
                                    <RadioButton
                                        id="type-page"
                                        v-model="form.type"
                                        value="page"
                                        :disabled="isEdit"
                                        @change="onTypeChange"
                                    />
                                    <label for="type-page" class="ml-2">Страница</label>
                                </div>
                                <div class="flex align-items-center">
                                    <RadioButton
                                        id="type-post"
                                        v-model="form.type"
                                        value="post"
                                        :disabled="isEdit"
                                        @change="onTypeChange"
                                    />
                                    <label for="type-post" class="ml-2">Запись (пост)</label>
                                </div>
                            </div>
                            <small class="text-500">Тип контента нельзя изменить после создания</small>
                        </div>

                        <div class="field mb-4">
                            <label class="font-bold block mb-2 required">Заголовок</label>
                            <InputText v-model="form.title" class="w-full" @input="generateSlug" />
                            <small class="text-500">Введите заголовок {{ typeText.toLowerCase() }}</small>
                        </div>

                        <div class="field mb-4">
                            <label class="font-bold block mb-2">Ссылка (URL)</label>
                            <div class="flex">
                                <span class="p-inputgroup-addon">{{ siteUrl }}/</span>
                                <InputText v-model="form.slug" class="flex-1" />
                            </div>
                            <small class="text-500">URL будет автоматически сгенерирован из заголовка</small>
                        </div>

                        <div class="field mb-4">
                            <label class="font-bold block mb-2">Контент</label>
                            <Editor
                                v-model="form.content"
                                editorStyle="height: 500px"
                                :modules="editorModules"
                                :toolbar="editorToolbar"
                            />
                        </div>

                        <div class="field mb-4">
                            <label class="font-bold block mb-2">Краткое описание</label>
                            <Textarea v-model="form.excerpt" rows="3" class="w-full" :maxlength="500" />
                            <small class="text-500">Используется в превью и для SEO (макс. 500 символов)</small>
                            <small class="text-500 float-right">{{ form.excerpt?.length || 0 }}/500</small>
                        </div>
                    </template>
                </Card>

                <!-- Блоки страницы (аккордеон по groups выбранного шаблона, teo/pages) -->
                <Card v-if="templateBlockGroups.length" class="mb-4">
                    <template #title>Блоки страницы</template>
                    <template #content>
                        <Accordion :multiple="true" :lazy="true" :active-index="[0]">
                            <AccordionTab v-for="group in templateBlockGroups" :key="group.key">
                                <template #header>
                                    <div class="flex align-items-center gap-2">
                                        <span>{{ group.title }}</span>
                                        <Tag v-if="isGroupFilled(group.key)" severity="success" value="заполнено" />
                                    </div>
                                </template>

                                <div class="flex justify-content-end align-items-center gap-2 mb-3">
                                    <span class="text-600 text-sm">Блок включён</span>
                                    <InputSwitch
                                        :model-value="blockEnabled(group.key)"
                                        @update:model-value="(value) => setBlockEnabled(group.key, value)"
                                    />
                                </div>

                                <AcfFieldGroup
                                    :group="group"
                                    v-model="acfValues"
                                />
                            </AccordionTab>
                        </Accordion>
                    </template>
                </Card>

                <!-- Кастомные поля (teo/acf), не вошедшие в блоки шаблона -->
                <Card v-if="otherAcfGroups.length" class="mb-4">
                    <template #title>Кастомные поля</template>
                    <template #content>
                        <AcfFieldGroup
                            v-for="group in otherAcfGroups"
                            :key="group.key"
                            v-model="acfValues"
                            :group="group"
                        />
                    </template>
                </Card>
            </div>

            <!-- Боковая колонка -->
            <div class="col-12 lg:col-4">
                <!-- Шаблон страницы (teo/pages) -->
                <Card class="mb-4">
                    <template #title>Шаблон</template>
                    <template #content>
                        <div class="field">
                            <label class="font-bold block mb-2">Шаблон страницы</label>
                            <Dropdown
                                v-model="form.meta.template"
                                :options="templateOptions"
                                optionLabel="title"
                                optionValue="key"
                                class="w-full"
                                placeholder="По умолчанию"
                                :showClear="true"
                            />
                            <small v-if="selectedTemplate?.description" class="text-500">
                                {{ selectedTemplate.description }}
                            </small>
                            <small v-else class="text-500">
                                Не указан — используется шаблон по умолчанию
                            </small>
                        </div>
                    </template>
                </Card>

                <!-- Публикация -->
                <Card class="mb-4">
                    <template #title>Публикация</template>
                    <template #content>
                        <div class="field mb-3">
                            <label class="font-bold block mb-2">Статус</label>
                            <Dropdown
                                v-model="form.status"
                                :options="statuses"
                                optionLabel="label"
                                optionValue="value"
                                class="w-full"
                            />
                        </div>

                        <!-- Родительская страница (только для страниц) -->
                        <div v-if="form.type === 'page'" class="field mb-3">
                            <label class="font-bold block mb-2">Родительская страница</label>
                            <Dropdown
                                v-model="form.parent_id"
                                :options="pages"
                                optionLabel="title"
                                optionValue="id"
                                class="w-full"
                                :filter="true"
                                placeholder="Нет (корневая страница)"
                            >
                                <template #option="{ option }">
                                    <div>{{ option.title }}</div>
                                </template>
                            </Dropdown>
                        </div>

                        <!-- Порядок сортировки (только для страниц) -->
                        <div v-if="form.type === 'page'" class="field mb-3">
                            <label class="font-bold block mb-2">Порядок сортировки</label>
                            <InputNumber v-model="form.order" class="w-full" :min="0" />
                        </div>

                        <!-- Категории/Таксономии (только для постов) -->
                        <div v-if="form.type === 'post'" class="field mb-3">
                            <label class="font-bold block mb-2">Категории</label>
                            <MultiSelect
                                v-model="form.categories"
                                :options="categories"
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Выберите категории"
                                class="w-full"
                                display="chip"
                            />
                        </div>

                        <!-- Теги (только для постов) -->
                        <div v-if="form.type === 'post'" class="field mb-3">
                            <label class="font-bold block mb-2">Теги</label>
                            <Chips
                                v-model="form.tags"
                                placeholder="Введите теги и нажмите Enter"
                                class="w-full"
                            />
                        </div>

                        <div class="field">
                            <label class="font-bold block mb-2">Дата публикации</label>
                            <Calendar
                                v-model="form.published_at"
                                dateFormat="dd.mm.yy"
                                class="w-full"
                                showTime
                                hourFormat="24"
                            />
                        </div>
                    </template>
                </Card>

                <!-- Изображение -->
                <Card class="mb-4">
                    <template #title>Изображение</template>
                    <template #content>
                        <div class="field">
                            <div v-if="form.featured_image" class="mb-3">
                                <img :src="form.featured_image" class="w-full border-round" style="max-height: 200px; object-fit: cover" />
                                <div class="flex gap-2 mt-2">
                                    <Button label="Изменить" icon="pi pi-refresh" class="p-button-text w-full" @click="openMediaLibrary" />
                                    <Button label="Удалить" icon="pi pi-trash" class="p-button-text p-button-danger w-full" @click="removeImage" />
                                </div>
                            </div>
                            <FileUpload
                                name="file"
                                :url="`${apiUrl}/media/upload`"
                                :auto="true"
                                :multiple="false"
                                accept="image/*"
                                @upload="onImageUpload"
                                @error="onUploadError"
                                :headers="uploadHeaders"
                            >
                                <template #empty>
                                    <i class="pi pi-image text-4xl text-500 mb-3"></i>
                                    <p class="text-500">Перетащите сюда или нажмите для выбора</p>
                                </template>
                            </FileUpload>
                        </div>
                    </template>
                </Card>

                <!-- SEO настройки -->
                <Card>
                    <template #title>SEO настройки</template>
                    <template #content>
                        <div class="field mb-3">
                            <label class="font-bold block mb-2">Meta Title</label>
                            <InputText v-model="form.meta.title" class="w-full" />
                            <small class="text-500">Если не указано, используется заголовок</small>
                        </div>

                        <div class="field mb-3">
                            <label class="font-bold block mb-2">Meta Description</label>
                            <Textarea v-model="form.meta.description" rows="3" class="w-full" :maxlength="160" />
                            <small class="text-500">Рекомендуемая длина: 150-160 символов</small>
                            <small class="text-500 float-right">{{ form.meta.description?.length || 0 }}/160</small>
                        </div>

                        <div class="field mb-3">
                            <label class="font-bold block mb-2">Meta Keywords</label>
                            <InputText v-model="form.meta.keywords" class="w-full" />
                            <small class="text-500">Ключевые слова через запятую</small>
                        </div>

                        <div class="field">
                            <label class="font-bold block mb-2">Канонический URL</label>
                            <InputText v-model="form.meta.canonical" class="w-full" />
                        </div>
                    </template>
                </Card>

                <!-- Действия (только для редактирования) -->
                <Card v-if="isEdit" class="mt-4">
                    <template #title>Действия</template>
                    <template #content>
                        <div class="flex flex-column gap-2">
                            <Button label="Создать копию" icon="pi pi-copy" class="p-button-outlined" @click="duplicate" />
                            <Button label="Экспорт" icon="pi pi-download" class="p-button-outlined" @click="exportPage" />
                            <Button label="Восстановить черновик" icon="pi pi-history" class="p-button-outlined" @click="loadDraft" />
                            <Button label="Удалить" icon="pi pi-trash" class="p-button-outlined p-button-danger" @click="deletePage" />
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <!-- Предпросмотр страницы в iframe (HTML строит teo/pages) -->
        <Dialog
            v-model:visible="previewVisible"
            :header="`Предпросмотр: ${form.title || 'без названия'}`"
            :modal="true"
            :style="{ width: '92vw' }"
        >
            <iframe
                v-if="previewVisible && previewHtml"
                :srcdoc="previewHtml"
                style="width: 100%; height: 72vh; border: 0;"
                title="Предпросмотр страницы"
            ></iframe>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'

import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import MultiSelect from 'primevue/multiselect'
import Chips from 'primevue/chips'
import InputNumber from 'primevue/inputnumber'
import Calendar from 'primevue/calendar'
import Button from 'primevue/button'
import FileUpload from 'primevue/fileupload'
import Editor from 'primevue/editor'
import RadioButton from 'primevue/radiobutton'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import Dialog from 'primevue/dialog'
import Accordion from 'primevue/accordion'
import AccordionTab from 'primevue/accordiontab'
import InputSwitch from 'primevue/inputswitch'
import axios from 'axios'
import { AcfFieldGroup, useAcfGroups, useAcfValues } from '@acf'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const siteUrl = window.location.origin
const apiUrl = '/api/admin'

const isEdit = computed(() => !!route.params.id)

// Определение типа из URL
const currentType = computed(() => {
    if (route.path.includes('/posts')) return 'post'
    return 'page'
})

const typeText = computed(() => form.value.type === 'page' ? 'страницы' : 'записи')

const getTypeSeverity = computed(() => {
    return form.value.type === 'page' ? 'info' : 'success'
})

const form = ref({
    id: null,
    title: '',
    slug: '',
    content: '',
    excerpt: '',
    type: currentType.value,
    status: 'draft',
    parent_id: null,
    order: 0,
    featured_image: null,
    published_at: null,
    categories: [],
    tags: [],
    meta: {
        title: '',
        description: '',
        keywords: '',
        canonical: '',
        template: null
    },
    author: null,
    created_at: null,
    updated_at: null,
    deleted_at: null
})

const pages = ref([])
const categories = ref([])

// ---- Кастомные поля (пакет teo/acf) ----
const acfGroups = ref([])
const acfValues = ref({})
const { fetchGroups } = useAcfGroups()
const { fetchValues, saveValues } = useAcfValues()

const loadAcf = async () => {
    try {
        const list = await fetchGroups({
            postType: form.value.type,
            slug: form.value.slug || '',
            template: form.value.meta?.template || null
        })
        acfGroups.value = list ?? []
    } catch (error) {
        console.error(error)
        acfGroups.value = []
    }

    if (!acfGroups.value.length) {
        acfValues.value = {}
        return
    }

    if (isEdit.value) {
        try {
            acfValues.value = (await fetchValues(form.value.type, route.params.id)) ?? {}
        } catch (error) {
            console.error(error)
            acfValues.value = {}
        }
    }
}

// Группа привязана к типу, slug (home) и шаблону — перезапрашиваем при их смене
watch(
    () => `${form.value.type}|${form.value.slug ?? ''}|${form.value.meta?.template ?? ''}`,
    () => loadAcf(),
    { immediate: true }
)

const statuses = [
    { label: 'Черновик', value: 'draft' },
    { label: 'Опубликовано', value: 'published' },
    { label: 'Приватно', value: 'private' }
]

const editorModules = {
    toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ header: 1 }, { header: 2 }],
        [{ list: 'ordered' }, { list: 'bullet' }],
        [{ script: 'sub' }, { script: 'super' }],
        [{ indent: '-1' }, { indent: '+1' }],
        [{ direction: 'rtl' }],
        [{ size: ['small', false, 'large', 'huge'] }],
        [{ header: [1, 2, 3, 4, 5, 6, false] }],
        [{ color: [] }, { background: [] }],
        [{ font: [] }],
        [{ align: [] }],
        ['clean'],
        ['link', 'image', 'video']
    ]
}

const editorToolbar = [
    ['bold', 'italic', 'underline', 'strike'],
    ['blockquote', 'code-block'],
    [{ header: 1 }, { header: 2 }],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['link', 'image']
]

// Заголовки для загрузки файлов
const uploadHeaders = computed(() => ({
    Authorization: `Bearer ${localStorage.getItem('token')}`
}))

const getToken = () => localStorage.getItem('token')

const generateSlug = () => {
    if (!form.value.slug || form.value.slug === '') {
        form.value.slug = form.value.title
            .toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
    }
}

const onTypeChange = () => {
    // Сброс специфичных полей при смене типа
    if (form.value.type === 'page') {
        form.value.categories = []
        form.value.tags = []
        form.value.order = form.value.order || 0
    } else {
        form.value.parent_id = null
        form.value.order = 0
    }
}

// ---- Шаблоны страниц (teo/pages) ----
const templateOptions = ref([])
const selectedTemplate = computed(() =>
    templateOptions.value.find((t) => t.key === form.value.meta?.template) ?? null
)

const loadTemplates = async () => {
    try {
        const token = getToken()
        const response = await axios.get(`/api/admin/page-templates?post_type=${form.value.type}`, {
            headers: { Authorization: `Bearer ${token}` }
        })
        templateOptions.value = Array.isArray(response.data) ? response.data : []
    } catch (error) {
        console.error(error)
        templateOptions.value = []
    }
}

// Список шаблонов зависит от типа контента — перезапрашиваем при смене
watch(() => form.value.type, loadTemplates, { immediate: true })

// ---- Блоки страницы: groups выбранного шаблона → аккордеон ----
const templateBlockGroups = computed(() => {
    const keys = selectedTemplate.value?.groups ?? []
    if (!keys.length) return []
    const byKey = Object.fromEntries(acfGroups.value.map((group) => [group.key, group]))
    return keys.map((key) => byKey[key]).filter(Boolean)
})

// Группы из локаций ACF, не заявленные в groups[] шаблона, — в «Кастомные поля»
const otherAcfGroups = computed(() => {
    const declared = new Set(templateBlockGroups.value.map((group) => group.key))
    return acfGroups.value.filter((group) => !declared.has(group.key))
})

// Dev: группы, незарегистрированные в шаблоне (ошибка конфигурации)
if (import.meta.env.DEV) {
    watch(
        [acfGroups, selectedTemplate],
        () => {
            const template = selectedTemplate.value
            if (!template?.groups?.length) return
            const declared = new Set(template.groups)
            for (const group of acfGroups.value) {
                if (!declared.has(group.key)) {
                    console.warn(
                        `[teo/pages] Группа ACF "${group.key}" не заявлена в groups[] шаблона "${template.key}"`
                    )
                }
            }
        },
        { immediate: true }
    )
}

const isEmptyValue = (value) =>
    value === null || value === undefined || value === ''
    || (Array.isArray(value) && value.length === 0)

const isGroupFilled = (groupKey) => {
    const group = acfGroups.value.find((item) => item.key === groupKey)
    if (!group) return false
    return (group.fields ?? []).some((field) => !isEmptyValue(acfValues.value?.[field.name]))
}

// Вкл/выкл блока: meta.blocks_enabled[groupKey] (по умолчанию включён)
const blockEnabled = (groupKey) => form.value.meta?.blocks_enabled?.[groupKey] !== false

const setBlockEnabled = (groupKey, value) => {
    if (!form.value.meta) form.value.meta = {}
    form.value.meta.blocks_enabled = {
        ...(form.value.meta.blocks_enabled ?? {}),
        [groupKey]: value
    }
}

const loadPage = async () => {
    try {
        const token = getToken()
        const endpoint = form.value.type === 'page' ? 'pages' : 'posts'
        const response = await axios.get(`/api/admin/${endpoint}/${route.params.id}`, {
            headers: { Authorization: `Bearer ${token}` }
        })

        form.value = response.data
        if (!form.value.meta) form.value.meta = {}
        if (!form.value.categories) form.value.categories = []
        if (!form.value.tags) form.value.tags = []
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось загрузить контент', life: 3000 })
    }
}

const loadPages = async () => {
    try {
        const token = getToken()
        const response = await axios.get('/api/admin/pages?per_page=100', {
            headers: { Authorization: `Bearer ${token}` }
        })
        pages.value = response.data.data.filter(p => p.id != route.params.id)
    } catch (error) {
        console.error(error)
    }
}

const loadCategories = async () => {
    try {
        const token = getToken()
        const response = await axios.get('/api/admin/taxonomies?type=category', {
            headers: { Authorization: `Bearer ${token}` }
        })
        categories.value = response.data.data || []
    } catch (error) {
        console.error(error)
    }
}

const savePage = async () => {
    try {
        const token = getToken()
        const endpoint = form.value.type === 'page' ? 'pages' : 'posts'

        // Подготовка данных
        const saveData = JSON.parse(JSON.stringify(form.value))

        // Очистка meta если массив
        if (Array.isArray(saveData.meta)) {
            saveData.meta = {
                title: form.value.meta?.title || '',
                description: form.value.meta?.description || '',
                keywords: form.value.meta?.keywords || '',
                canonical: form.value.meta?.canonical || '',
                template: form.value.meta?.template || null,
                blocks_enabled: form.value.meta?.blocks_enabled || null
            }
        }

        let savedId = null
        let created = false

        if (isEdit.value) {
            await axios.put(`/api/admin/${endpoint}/${route.params.id}`, saveData, {
                headers: { Authorization: `Bearer ${token}` }
            })
            savedId = Number(route.params.id)
            toast.add({ severity: 'success', summary: 'Успех', detail: `${typeText.value.slice(0, -1)} обновлена`, life: 3000 })
        } else {
            const response = await axios.post(`/api/admin/${endpoint}`, saveData, {
                headers: { Authorization: `Bearer ${token}` }
            })
            savedId = response.data?.id ?? response.data?.data?.id ?? null
            created = true
        }

        // Сохранение значений кастомных полей (teo/acf)
        if (savedId && acfGroups.value.length) {
            try {
                await saveValues(form.value.type, savedId, acfValues.value)
            } catch (error) {
                console.error(error)
                toast.add({ severity: 'warn', summary: 'Внимание', detail: 'Не удалось сохранить кастомные поля', life: 3000 })
            }
        }

        if (created) {
            toast.add({ severity: 'success', summary: 'Успех', detail: `${typeText.value.slice(0, -1)} создана`, life: 3000 })
            router.push(`/admin/${endpoint}`)
        }
    } catch (error) {
        console.error(error)
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось сохранить', life: 3000 })
    }
}

const duplicate = async () => {
    try {
        const token = getToken()
        const endpoint = form.value.type === 'page' ? 'pages' : 'posts'
        await axios.post(`/api/admin/${endpoint}/${route.params.id}/duplicate`, {}, {
            headers: { Authorization: `Bearer ${token}` }
        })
        toast.add({ severity: 'success', summary: 'Успех', detail: `${typeText.value.slice(0, -1)} скопирована`, life: 3000 })
        router.push(`/admin/${endpoint}`)
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось скопировать', life: 3000 })
    }
}

const deletePage = async () => {
    if (!confirm(`Удалить ${typeText.value.slice(0, -1)} "${form.value.title}"?`)) return

    try {
        const token = getToken()
        const endpoint = form.value.type === 'page' ? 'pages' : 'posts'
        await axios.delete(`/api/admin/${endpoint}/${route.params.id}`, {
            headers: { Authorization: `Bearer ${token}` }
        })
        toast.add({ severity: 'success', summary: 'Успех', detail: `${typeText.value.slice(0, -1)} удалена`, life: 3000 })
        router.push(`/admin/${endpoint}`)
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось удалить', life: 3000 })
    }
}

const restorePage = async () => {
    try {
        const token = getToken()
        const endpoint = form.value.type === 'page' ? 'pages' : 'posts'
        await axios.post(`/api/admin/${endpoint}/${route.params.id}/restore`, {}, {
            headers: { Authorization: `Bearer ${token}` }
        })
        toast.add({ severity: 'success', summary: 'Успех', detail: `${typeText.value.slice(0, -1)} восстановлена`, life: 3000 })
        loadPage()
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось восстановить', life: 3000 })
    }
}

const publish = async () => {
    form.value.status = 'published'
    if (!form.value.published_at) {
        form.value.published_at = new Date()
    }
    await savePage()
}

const exportPage = () => {
    const dataStr = JSON.stringify(form.value, null, 2)
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr)
    const exportFileDefaultName = `${form.value.slug}.json`

    const linkElement = document.createElement('a')
    linkElement.setAttribute('href', dataUri)
    linkElement.setAttribute('download', exportFileDefaultName)
    linkElement.click()

    toast.add({ severity: 'success', summary: 'Успех', detail: 'Экспорт выполнен', life: 3000 })
}

const loadDraft = () => {
    // Здесь можно добавить загрузку черновика из localStorage или истории версий
    toast.add({ severity: 'info', summary: 'Информация', detail: 'Функция в разработке', life: 3000 })
}

const openMediaLibrary = () => {
    // Здесь можно открыть медиа-библиотеку
    toast.add({ severity: 'info', summary: 'Информация', detail: 'Медиа-библиотека в разработке', life: 3000 })
}

const previewVisible = ref(false)
const previewHtml = ref('')

// Предпросмотр: HTML рендерит teo/pages по снапшоту формы
// (тот же шаблон, поля и SEO-мета, что у публичной страницы)
const preview = async () => {
    try {
        const token = getToken()
        const payload = {
            title: form.value.title,
            slug: form.value.slug,
            content: form.value.content,
            excerpt: form.value.excerpt,
            type: form.value.type,
            parent_id: form.value.parent_id,
            featured_image: form.value.featured_image,
            published_at: form.value.published_at,
            meta: { ...(form.value.meta ?? {}) },
            acf: { ...acfValues.value }
        }
        const response = await axios.post('/api/admin/pages/preview', payload, {
            headers: { Authorization: `Bearer ${token}` },
            responseType: 'text'
        })
        previewHtml.value = response.data
        previewVisible.value = true
    } catch (error) {
        console.error(error)
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось построить предпросмотр', life: 3000 })
    }
}

// Подписанная ссылка на черновик (без публикации) — в буфер обмена
const copyDraftLink = async () => {
    try {
        const token = getToken()
        const response = await axios.post(
            '/api/admin/pages/preview-link',
            { id: route.params.id },
            { headers: { Authorization: `Bearer ${token}` } }
        )
        await navigator.clipboard.writeText(response.data.url)
        toast.add({
            severity: 'success',
            summary: 'Ссылка-черновик скопирована',
            detail: `Действует до ${new Date(response.data.expires_at).toLocaleString('ru-RU')}`,
            life: 5000
        })
    } catch (error) {
        console.error(error)
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось создать ссылку-черновик', life: 3000 })
    }
}

const onImageUpload = (event) => {
    if (event.xhr && event.xhr.response) {
        try {
            const response = JSON.parse(event.xhr.response)
            if (response.url) {
                form.value.featured_image = response.url
                toast.add({ severity: 'success', summary: 'Успех', detail: 'Изображение загружено', life: 3000 })
            }
        } catch (e) {
            console.error(e)
        }
    }
}

const onUploadError = (error) => {
    console.error(error)
    toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось загрузить изображение', life: 3000 })
}

const removeImage = () => {
    form.value.featured_image = null
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleString('ru-RU')
}

const goBack = () => {
    const endpoint = form.value.type === 'page' ? 'pages' : 'posts'
    router.push(`/admin/${endpoint}`)
}

onMounted(() => {
    if (isEdit.value) {
        loadPage()
    }
    loadPages()
    loadCategories()
})
</script>

<style scoped>
.required:after {
    content: ' *';
    color: red;
}

:deep(.ql-editor) {
    min-height: 300px;
}

:deep(.p-card) {
    margin-bottom: 0;
}

.gap-2 {
    gap: 0.5rem;
}

.gap-3 {
    gap: 1rem;
}

.ml-2 {
    margin-left: 0.5rem;
}

.ml-3 {
    margin-left: 1rem;
}

.mt-2 {
    margin-top: 0.5rem;
}

.mt-4 {
    margin-top: 1.5rem;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.w-full {
    width: 100%;
}

.float-right {
    float: right;
}
</style>
