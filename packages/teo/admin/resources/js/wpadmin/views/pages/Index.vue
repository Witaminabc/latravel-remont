<template>
    <div class="pages-index">
        <div class="flex justify-content-between align-items-center mb-4">
            <h1>Страницы</h1>
            <Button label="Новая страница" icon="pi pi-plus" @click="$router.push('/admin/pages/create')" />
        </div>

        <DataTable
            :value="pages"
            :loading="loading"
            :paginator="true"
            :rows="15"
            responsiveLayout="scroll"
            sortField="order"
            :sortOrder="1"
            @row-reorder="onRowReorder"
            reorderableRows
        >
            <Column field="order" header="Порядок" reorderable></Column>
            <Column field="title" header="Заголовок">
                <template #body="{ data }">
                    <div>
                        <strong>{{ data.title }}</strong>
                        <br />
                        <small class="text-500">{{ data.slug }}</small>
                    </div>
                </template>
            </Column>
            <Column field="status" header="Статус">
                <template #body="{ data }">
                    <Tag :severity="getStatusSeverity(data.status)" :value="getStatusText(data.status)" />
                </template>
            </Column>
            <Column field="author.name" header="Автор"></Column>
            <Column field="updated_at" header="Обновлено">
                <template #body="{ data }">
                    {{ formatDate(data.updated_at) }}
                </template>
            </Column>
            <Column header="Действия">
                <template #body="{ data }">
                    <Button icon="pi pi-eye" class="p-button-text" @click="viewPage(data)" />
                    <Button icon="pi pi-pencil" class="p-button-text" @click="editPage(data)" />
                    <Button icon="pi pi-copy" class="p-button-text" @click="duplicatePage(data)" />
                    <Button icon="pi pi-trash" class="p-button-text p-button-danger" @click="deletePage(data)" />
                </template>
            </Column>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import api from '../../services/api'
import axios from "axios";

const router = useRouter()
const toast = useToast()
const pages = ref([])
const loading = ref(false)

const fetchPages = async () => {

    loading.value = true
    try {
        // const response = await api.get('/admin/pages')
        const token = localStorage.getItem('token')
        const  response= await axios.get(`/api/admin/pages`,{
            headers: { Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json'}
        })
        pages.value = response.data.data
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось загрузить страницы', life: 3000 })
    } finally {
        loading.value = false
    }
}

const editPage = (page) => {
    router.push(`/admin/pages/${page.id}/edit`)
}

const viewPage = (page) => {
    window.open(page.view_url, '_blank')
}

const duplicatePage = async (page) => {
    try {
        await api.post(`/admin/pages/${page.id}/duplicate`)
        toast.add({ severity: 'success', summary: 'Успех', detail: 'Страница скопирована', life: 3000 })
        fetchPages()
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось скопировать страницу', life: 3000 })
    }
}

const deletePage = async (page) => {
    console.log("deletePage")
    console.log(page.id)
    if (confirm(`Удалить страницу "${page.title}"?`)) {
        try {
            console.log("deletePage try")
            // await api.delete(`/api/admin/pages/${page.id}`)
            const token = localStorage.getItem('token')

            await axios.delete(`/api/admin/pages/${page.id}`,{
                headers: { Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json'}
            })


            toast.add({ severity: 'success', summary: 'Успех', detail: 'Страница удалена', life: 3000 })
            fetchPages()
        } catch (error) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось удалить страницу', life: 3000 })
        }
    }
}

const onRowReorder = async (event) => {
    pages.value = event.value
    const orders = pages.value.map((page, index) => ({ id: page.id, order: index }))
    try {
        await api.post('/admin/pages/reorder', { orders })
        toast.add({ severity: 'success', summary: 'Успех', detail: 'Порядок обновлен', life: 3000 })
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось обновить порядок', life: 3000 })
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
    return new Date(date).toLocaleDateString('ru-RU')
}

onMounted(fetchPages)
</script>
