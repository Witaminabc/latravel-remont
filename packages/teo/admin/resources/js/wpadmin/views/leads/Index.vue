<template>
    <div class="leads-page">
        <div class="leads-header">
            <h1 class="leads-title">Заявки</h1>
            <select v-model="status" class="leads-filter" @change="load(1)">
                <option value="all">Все</option>
                <option value="new">Новые</option>
                <option value="processed">Обработанные</option>
            </select>
        </div>
        <div v-if="loading" class="leads-empty">Загрузка...</div>
        <div v-else class="leads-table-wrap">
            <table class="leads-table">
                <thead>
                    <tr><th>Клиент</th><th>Телефон</th><th>Дата</th><th>Статус</th></tr>
                </thead>
                <tbody>
                    <tr v-for="lead in leads" :key="lead.id">
                        <td>
                            <div class="leads-name">{{ lead.name }}</div>
                            <div v-if="lead.email" class="leads-sub">{{ lead.email }}</div>
                            <div v-if="lead.message" class="leads-sub">{{ lead.message }}</div>
                        </td>
                        <td><a :href="'tel:' + lead.phone">{{ lead.phone }}</a></td>
                        <td>{{ fmt(lead.created_at) }}</td>
                        <td>
                            <span class="leads-badge" :class="lead.status">{{ lead.status === 'new' ? 'Новая' : 'Обработана' }}</span>
                            <button class="leads-btn" @click="toggle(lead)">{{ lead.status === 'new' ? 'Обработать' : 'Вернуть' }}</button>
                        </td>
                    </tr>
                    <tr v-if="!leads.length"><td colspan="4" class="leads-empty">Заявок нет</td></tr>
                </tbody>
            </table>
        </div>
        <div v-if="page.last_page > 1" class="leads-pager">
            <button :disabled="page.current_page <= 1" @click="load(page.current_page - 1)">Назад</button>
            <span>{{ page.current_page }} / {{ page.last_page }}</span>
            <button :disabled="page.current_page >= page.last_page" @click="load(page.current_page + 1)">Вперёд</button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const leads = ref([])
const loading = ref(true)
const status = ref('all')
const page = ref({ current_page: 1, last_page: 1 })

async function load(p = 1) {
    loading.value = true
    try {
        const { data } = await api.get('admin/leads', { params: { status: status.value, page: p } })
        leads.value = data.data
        page.value = { current_page: data.current_page, last_page: data.last_page }
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

async function toggle(lead) {
    const ns = lead.status === 'new' ? 'processed' : 'new'
    try {
        await api.patch('admin/leads/' + lead.id + '/status', { status: ns })
        lead.status = ns
    } catch (e) {
        console.error(e)
    }
}

function fmt(d) {
    if (!d) return ''
    const x = new Date(d)
    return x.toLocaleDateString('ru-RU') + ' ' + x.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => load(1))
</script>

<style scoped>
.leads-page { padding: 0; }
.leads-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; gap: 12px; flex-wrap: wrap; }
.leads-title { margin: 0; font-size: 1.5rem; color: #0f172a; }
.leads-filter { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; font-size: .95rem; }
.leads-table-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: auto; }
.leads-table { width: 100%; border-collapse: collapse; font-size: .95rem; }
.leads-table th { text-align: left; padding: 12px 16px; background: #f8fafc; color: #64748b; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid #e2e8f0; }
.leads-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: top; color: #0f172a; }
.leads-table tr:last-child td { border-bottom: none; }
.leads-name { font-weight: 600; }
.leads-sub { color: #64748b; font-size: .85rem; margin-top: 2px; max-width: 360px; white-space: pre-line; }
.leads-table a { color: #2563eb; text-decoration: none; }
.leads-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: .78rem; font-weight: 600; }
.leads-badge.new { background: #fef3c7; color: #92400e; }
.leads-badge.processed { background: #dcfce7; color: #166534; }
.leads-btn { margin-left: 10px; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; font-size: .82rem; cursor: pointer; }
.leads-btn:hover { background: #f1f5f9; }
.leads-empty { padding: 28px; text-align: center; color: #64748b; }
.leads-pager { display: flex; align-items: center; gap: 12px; justify-content: center; margin-top: 16px; }
.leads-pager button { padding: 8px 14px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; cursor: pointer; }
.leads-pager button:disabled { opacity: .5; cursor: default; }
</style>
