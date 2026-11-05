<!-- components/ContentList.vue -->
<template>
    <div class="content-index">
        <div class="flex justify-content-between align-items-center mb-4">
            <h1>{{ title }}</h1>
            <Button
                :label="`Новая ${typeText}`"
                icon="pi pi-plus"
                @click="$router.push(`/admin/${type}s/create`)"
            />
        </div>

        <DataTable
            :value="items"
            :loading="loading"
            :paginator="true"
            :rows="15"
            responsiveLayout="scroll"
            :sortField="sortField"
            :sortOrder="sortOrder"
            :reorderableRows="type === 'page'"
            @row-reorder="onRowReorder"
        >
            <!-- Колонка порядка только для страниц -->
            <Column
                v-if="type === 'page'"
                field="order"
                header="Порядок"
                reorderable
            ></Column>

            <!-- Заголовок -->
            <Column field="title" header="Заголовок">
                <template #body="{ data }">
                    <div>
                        <strong>{{ data.title }}</strong>
                        <br />
                        <small class="text-500">{{ data.slug }}</small>
                    </div>
                </template>
            </Column>

            <!-- Дата публикации для постов -->
            <Column v-if="type === 'post'" field="published_at" header="Дата публикации">
                <template #body="{ data }">
                    {{ formatDate(data.published_at) }}
                </template>
            </Column>

            <!-- Статус -->
            <Column field="status" header="Статус">
                <template #body="{ data }">
                    <Tag :severity="getStatusSeverity(data.status)" :value="getStatusText(data.status)" />
                </template>
            </Column>

            <!-- Автор -->
            <Column field="author.name" header="Автор"></Column>

            <!-- Обновлено -->
            <Column field="updated_at" header="Обновлено">
                <template #body="{ data }">
                    {{ formatDate(data.updated_at) }}
                </template>
            </Column>

            <!-- Действия -->
            <Column header="Действия">
                <template #body="{ data }">
                    <Button icon="pi pi-eye" class="p-button-text" @click="viewItem(data)" />
                    <Button icon="pi pi-pencil" class="p-button-text" @click="editItem(data)" />
                    <Button icon="pi pi-copy" class="p-button-text" @click="duplicateItem(data)" />
                    <Button icon="pi pi-trash" class="p-button-text p-button-danger" @click="deleteItem(data)" />
                </template>
            </Column>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import axios from "axios"

const props = defineProps({
    type: {
        type: String,
        required: true,
        validator: (value) => ['page', 'post'].includes(value)
    }
})

const router = useRouter()
const toast = useToast()
const items = ref([])
const loading = ref(false)

// Вычисляемые свойства для разных типов
const title = computed(() => props.type === 'page' ? 'Страницы' : 'Записи')
const typeText = computed(() => props.type === 'page' ? 'страницу' : 'запись')
const sortField = computed(() => props.type === 'page' ? 'order' : 'published_at')
const sortOrder = computed(() => props.type === 'page' ? 1 : -1)

const fetchItems = async () => {
    loading.value = true
    try {
        const token = localStorage.getItem('token')
        const response = await axios.get(`/api/admin/${props.type}s`, {
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        })
        items.value = response.data.data
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Ошибка',
            detail: `Не удалось загрузить ${title.value.toLowerCase()}`,
            life: 3000
        })
    } finally {
        loading.value = false
    }
}

const editItem = (item) => {
    router.push(`/admin/${props.type}s/${item.id}/edit`)
}

const viewItem = (item) => {
    window.open(item.view_url, '_blank')
}

const duplicateItem = async (item) => {
    try {
        const token = localStorage.getItem('token')
        await axios.post(`/api/admin/${props.type}s/${item.id}/duplicate`, {}, {
            headers: { Authorization: `Bearer ${token}` }
        })
        toast.add({
            severity: 'success',
            summary: 'Успех',
            detail: `${title.value.slice(0, -1)} скопирован${props.type === 'page' ? 'а' : 'а'}`,
            life: 3000
        })
        fetchItems()
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Ошибка',
            detail: `Не удалось скопировать ${title.value.toLowerCase()}`,
            life: 3000
        })
    }
}

const deleteItem = async (item) => {
    if (confirm(`Удалить "${item.title}"?`)) {
        try {
            const token = localStorage.getItem('token')
            await axios.delete(`/api/admin/${props.type}s/${item.id}`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            })
            toast.add({
                severity: 'success',
                summary: 'Успех',
                detail: `${title.value.slice(0, -1)} удален${props.type === 'page' ? 'а' : 'а'}`,
                life: 3000
            })
            fetchItems()
        } catch (error) {
            toast.add({
                severity: 'error',
                summary: 'Ошибка',
                detail: `Не удалось удалить ${title.value.toLowerCase()}`,
                life: 3000
            })
        }
    }
}

const onRowReorder = async (event) => {
    if (props.type !== 'page') return

    items.value = event.value
    const orders = items.value.map((item, index) => ({ id: item.id, order: index }))
    try {
        const token = localStorage.getItem('token')
        await axios.post('/api/admin/pages/reorder', { orders }, {
            headers: { Authorization: `Bearer ${token}` }
        })
        toast.add({
            severity: 'success',
            summary: 'Успех',
            detail: 'Порядок страниц обновлен',
            life: 3000
        })
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Ошибка',
            detail: 'Не удалось обновить порядок',
            life: 3000
        })
    }
}

const getStatusSeverity = (status) => {
    const severities = { draft: 'warning', published: 'success', private: 'info' }
    return severities[status] || 'secondary'
}

const getStatusText = (status) => {
    const texts = { draft: 'Черновик', published: 'Опубликовано', private: 'Приватно' }
    return texts[status] || status
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('ru-RU')
}

onMounted(fetchItems)
</script>
