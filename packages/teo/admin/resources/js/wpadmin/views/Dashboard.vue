<template>
    <div class="dashboard">
        <h1 class="text-3xl font-bold mb-6">Дашборд</h1>

        <div class="grid">
            <div class="col-12 md:col-6 lg:col-3">
                <Card class="stat-card">
                    <template #content>
                        <div class="flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-label">Всего страниц</div>
                                <div class="stat-value">{{ stats.total_pages || 0 }}</div>
                            </div>
                            <i class="pi pi-file text-4xl text-blue-500"></i>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="col-12 md:col-6 lg:col-3">
                <Card class="stat-card">
                    <template #content>
                        <div class="flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-label">Опубликовано</div>
                                <div class="stat-value">{{ stats.published_pages || 0 }}</div>
                            </div>
                            <i class="pi pi-check-circle text-4xl text-green-500"></i>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="col-12 md:col-6 lg:col-3">
                <Card class="stat-card">
                    <template #content>
                        <div class="flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-label">Черновики</div>
                                <div class="stat-value">{{ stats.draft_pages || 0 }}</div>
                            </div>
                            <i class="pi pi-pencil text-4xl text-orange-500"></i>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="col-12 md:col-6 lg:col-3">
                <Card class="stat-card">
                    <template #content>
                        <div class="flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-label">Пользователи</div>
                                <div class="stat-value">{{ stats.total_users || 0 }}</div>
                            </div>
                            <i class="pi pi-users text-4xl text-purple-500"></i>
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <div class="grid mt-4">
            <div class="col-12 lg:col-6">
                <Card>
                    <template #title>Последние страницы</template>
                    <template #content>
                        <DataTable :value="stats.recent_pages || []" class="p-datatable-sm">
                            <Column field="title" header="Заголовок"></Column>
                            <Column field="status" header="Статус">
                                <template #body="{ data }">
                                    <Tag :severity="getStatusSeverity(data.status)">
                                        {{ getStatusText(data.status) }}
                                    </Tag>
                                </template>
                            </Column>
                            <Column field="updated_at" header="Обновлено">
                                <template #body="{ data }">
                                    {{ formatDate(data.updated_at) }}
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>
            </div>

            <div class="col-12 lg:col-6">
                <Card>
                    <template #title>Быстрые действия</template>
                    <template #content>
                        <div class="flex flex-column gap-3">
                            <Button label="Создать новую страницу" icon="pi pi-plus" class="p-button-primary" @click="$router.push('/admin/pages/create')" />
                            <Button label="Загрузить медиа" icon="pi pi-image" class="p-button-secondary" @click="$router.push('/admin/media')" />
                            <Button label="Настройки сайта" icon="pi pi-cog" class="p-button-secondary" @click="$router.push('/admin/settings')" />
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
// import api from '../services/api'

import { useAuthStore } from '../../stores/auth'
import api from '../services/api'

const authStore = useAuthStore()


const router = useRouter()
const stats = ref({})

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

const fetchStats = async () => {
    try {
        const response = await api.get('/admin/dashboard/stats')
        stats.value = response.data
    } catch (error) {
        console.error('Error fetching stats:', error)
    }
}

onMounted(fetchStats)
</script>

<style scoped>
.stat-card {
    cursor: pointer;
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-label {
    font-size: 14px;
    color: #64748b;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #1e293b;
}
</style>
