<template>
    <div class="acf-groups">
        <div class="flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold mb-1">Группы кастомных полей</h1>
                <p class="text-color-secondary m-0">
                    Здесь управляется структура групп (как в ACF). Значения полей вводятся в форме страницы или записи.
                </p>
            </div>
            <Button label="Создать группу" icon="pi pi-plus" @click="create" />
        </div>

        <Card>
            <template #content>
                <DataTable :value="groups" :loading="loading" class="p-datatable-sm" data-key="key">
                    <Column header="Название">
                        <template #body="{ data }">
                            <router-link
                                :to="{ name: 'acf-groups.edit', params: { key: data.key } }"
                                class="font-semibold no-underline"
                            >
                                {{ data.title }}
                            </router-link>
                        </template>
                    </Column>
                    <Column header="Ключ">
                        <template #body="{ data }">
                            <code class="acf-groups__key">{{ data.key }}</code>
                        </template>
                    </Column>
                    <Column header="Условия отображения">
                        <template #body="{ data }">
                            {{ formatLocation(data.location) }}
                        </template>
                    </Column>
                    <Column header="Полей" style="width: 90px">
                        <template #body="{ data }">
                            {{ (data.fields || []).length }}
                        </template>
                    </Column>
                    <Column header="Источник" style="width: 130px">
                        <template #body="{ data }">
                            <Tag
                                :value="data.source === 'database' ? 'Админка' : 'Код'"
                                :severity="data.source === 'database' ? 'success' : 'info'"
                            />
                        </template>
                    </Column>
                    <Column header="Действия" style="width: 150px">
                        <template #body="{ data }">
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-text"
                                title="Редактировать"
                                @click="edit(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-text p-button-danger"
                                title="Удалить"
                                @click="remove(data)"
                            />
                        </template>
                    </Column>

                    <template #empty>
                        <div class="text-center text-color-secondary py-3">
                            Групп пока нет. Создайте первую группу полей.
                        </div>
                    </template>
                </DataTable>
            </template>
        </Card>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import api from '../../services/api'

const router = useRouter()
const toast = useToast()

const groups = ref([])
const loading = ref(false)

const locationLabels = {
    post_type: 'Тип материала',
    slug: 'Слаг'
}

const formatLocation = (location) => {
    const rules = Array.isArray(location) ? location : []
    if (!rules.length) return 'Все материалы'
    return rules
        .map((rule) => `${locationLabels[rule.type] || rule.type}: ${rule.value || '—'}`)
        .join(' · ')
}

const fetchGroups = async () => {
    loading.value = true
    try {
        const response = await api.get('/admin/acf/groups')
        groups.value = response.data?.data ?? response.data ?? []
    } catch (error) {
        console.error(error)
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось загрузить группы полей', life: 3000 })
    } finally {
        loading.value = false
    }
}

const create = () => router.push({ name: 'acf-groups.create' })
const edit = (group) => router.push({ name: 'acf-groups.edit', params: { key: group.key } })

const remove = async (group) => {
    if (!confirm(`Удалить группу «${group.title}»?`)) return

    try {
        await api.delete(`/admin/acf/groups/${group.key}`)
        toast.add({ severity: 'success', summary: 'Успех', detail: 'Группа удалена', life: 3000 })
        await fetchGroups()
    } catch (error) {
        console.error(error)
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось удалить группу', life: 3000 })
    }
}

onMounted(fetchGroups)
</script>

<style scoped>
.acf-groups__key {
    background: var(--surface-100);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.85rem;
}
</style>