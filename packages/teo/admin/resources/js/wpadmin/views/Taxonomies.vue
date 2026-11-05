<template>
    <div class="taxonomies">
        <div class="flex justify-content-between align-items-center mb-4">
            <h1 class="text-2xl font-bold">Категории и теги</h1>
            <Button label="Добавить" icon="pi pi-plus" @click="openDialog()" />
        </div>

        <div class="grid">
            <div class="col-6">
                <Card>
                    <template #title>Категории</template>
                    <template #content>
                        <DataTable :value="categories" class="p-datatable-sm">
                            <Column field="name" header="Название"></Column>
                            <Column field="slug" header="Слаг"></Column>
                            <Column header="Действия" style="width: 100px">
                                <template #body="{ data }">
                                    <Button icon="pi pi-pencil" class="p-button-text" @click="openDialog(data)" />
                                    <Button icon="pi pi-trash" class="p-button-text p-button-danger" @click="deleteTaxonomy(data)" />
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>
            </div>

            <div class="col-6">
                <Card>
                    <template #title>Теги</template>
                    <template #content>
                        <DataTable :value="tags" class="p-datatable-sm">
                            <Column field="name" header="Название"></Column>
                            <Column field="slug" header="Слаг"></Column>
                            <Column header="Действия" style="width: 100px">
                                <template #body="{ data }">
                                    <Button icon="pi pi-pencil" class="p-button-text" @click="openDialog(data)" />
                                    <Button icon="pi pi-trash" class="p-button-text p-button-danger" @click="deleteTaxonomy(data)" />
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>
            </div>
        </div>

        <Dialog v-model:visible="dialogVisible" :header="dialogTitle" :modal="true" :style="{ width: '450px' }">
            <div class="field mb-3">
                <label>Название</label>
                <InputText v-model="currentItem.name" class="w-full" />
            </div>
            <div class="field mb-3">
                <label>Слаг</label>
                <InputText v-model="currentItem.slug" class="w-full" />
            </div>
            <div class="field mb-3">
                <label>Тип</label>
                <Dropdown v-model="currentItem.type" :options="types" optionLabel="label" optionValue="value" class="w-full" />
            </div>

            <template #footer>
                <Button label="Отмена" icon="pi pi-times" class="p-button-text" @click="dialogVisible = false" />
                <Button label="Сохранить" icon="pi pi-check" class="p-button-primary" @click="saveTaxonomy" />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import api  from '../services/api'

const toast = useToast()
const categories = ref([])
const tags = ref([])
const dialogVisible = ref(false)
const currentItem = ref({ name: '', slug: '', type: 'category' })
const editingId = ref(null)

const types = [
    { label: 'Категория', value: 'category' },
    { label: 'Тег', value: 'tag' }
]

const dialogTitle = computed(() => {
    return editingId.value ? 'Редактировать' : 'Добавить'
})

const fetchTaxonomies = async () => {
    try {
        const response = await api.get('/admin/taxonomies')
        categories.value = response.data.filter(t => t.type === 'category')
        tags.value = response.data.filter(t => t.type === 'tag')
        console.log(response)
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось загрузить данные', life: 3000 })
    }
}

const openDialog = (item = null) => {
    if (item) {
        editingId.value = item.id
        currentItem.value = {
            ...item,
            type: item.type // item.type - строка, подойдет для optionValue
        }
    } else {
        editingId.value = null
        currentItem.value = { name: '', slug: '', type: 'category' }
    }
    dialogVisible.value = true
}

const saveTaxonomy = async () => {
    try {
        if (editingId.value) {
            await api.put(`/admin/taxonomies/${editingId.value}`, currentItem.value)
            toast.add({ severity: 'success', summary: 'Успех', detail: 'Обновлено', life: 3000 })
        } else {
            await api.post('/admin/taxonomies', currentItem.value)
            toast.add({ severity: 'success', summary: 'Успех', detail: 'Создано', life: 3000 })
        }
        dialogVisible.value = false
        fetchTaxonomies()
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось сохранить', life: 3000 })
    }
}

const deleteTaxonomy = async (item) => {
    if (confirm(`Удалить "${item.name}"?`)) {
        try {
            await api.delete(`/admin/taxonomies/${item.id}`)
            toast.add({ severity: 'success', summary: 'Успех', detail: 'Удалено', life: 3000 })
            fetchTaxonomies()
        } catch (error) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось удалить', life: 3000 })
        }
    }
}

onMounted(fetchTaxonomies)
</script>
