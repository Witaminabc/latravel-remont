<template>
    <div class="users">
        <div class="flex justify-content-between align-items-center mb-4">
            <h1 class="text-2xl font-bold">Пользователи</h1>
            <Button label="Добавить" icon="pi pi-plus" @click="openDialog()" />
        </div>

        <Card>
            <template #content>
                <DataTable :value="users" :loading="loading" :paginator="true" :rows="15">
                    <Column field="name" header="Имя">
                        <template #body="{ data }">
                            <div class="flex align-items-center gap-2">
                                <Avatar :label="data.name.charAt(0)" :style="{ backgroundColor: getRandomColor(data.id) }" />
                                <div>
                                    <div>{{ data.name }}</div>
                                    <small class="text-500">{{ data.email }}</small>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="role" header="Роль">
                        <template #body="{ data }">
                            <Tag :value="data.role === 'admin' ? 'Администратор' : (data.role === 'editor' ? 'Редактор' : 'Пользователь')" :severity="data.role === 'admin' ? 'danger' : 'info'" />
                        </template>
                    </Column>
                    <Column field="created_at" header="Зарегистрирован">
                        <template #body="{ data }">
                            {{ formatDate(data.created_at) }}
                        </template>
                    </Column>
                    <Column header="Действия" style="width: 120px">
                        <template #body="{ data }">
                            <Button icon="pi pi-pencil" class="p-button-text" @click="openDialog(data)" />
                            <Button icon="pi pi-trash" class="p-button-text p-button-danger" @click="deleteUser(data)" />
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <Dialog v-model:visible="dialogVisible" :header="dialogTitle" :modal="true" :style="{ width: '500px' }">
            <div class="field mb-3">
                <label>Имя</label>
                <InputText v-model="currentUser.name" class="w-full" />
            </div>
            <div class="field mb-3">
                <label>Email</label>
                <InputText v-model="currentUser.email" class="w-full" />
            </div>
            <div class="field mb-3" v-if="!editingId">
                <label>Пароль</label>
                <InputText type="password" v-model="currentUser.password" class="w-full" />
            </div>
            <div class="field mb-3">
                <label>
                    <Checkbox v-model="currentUser.is_admin" :binary="true" />
                    <span class="ml-2">Администратор</span>
                </label>
            </div>

            <template #footer>
                <Button label="Отмена" icon="pi pi-times" class="p-button-text" @click="dialogVisible = false" />
                <Button label="Сохранить" icon="pi pi-check" class="p-button-primary" @click="saveUser" />
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
import Tag from 'primevue/tag'
import Avatar from 'primevue/avatar'
import Checkbox from 'primevue/checkbox'
import api from '../services/api'

const toast = useToast()
const users = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const editingId = ref(null)
const currentUser = ref({ name: '', email: '', password: '', is_admin: false })

const dialogTitle = computed(() => {
    return editingId.value ? 'Редактировать пользователя' : 'Добавить пользователя'
})

const fetchUsers = async () => {
    loading.value = true
    try {
        const response = await api.get('/admin/users')
        users.value = response.data
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось загрузить пользователей', life: 3000 })
    } finally {
        loading.value = false
    }
}

const openDialog = (user = null) => {
    if (user) {
        editingId.value = user.id
        currentUser.value = { ...user, password: '', is_admin: user.role === 'admin' }
    } else {
        editingId.value = null
        currentUser.value = { name: '', email: '', password: '', is_admin: false }
    }
    dialogVisible.value = true
}

const saveUser = async () => {
    try {
        const { is_admin, password, ...rest } = currentUser.value
        const payload = { ...rest, role: is_admin ? 'admin' : 'user' }

        if (password) {
            payload.password = password
            payload.password_confirmation = password
        }

        if (editingId.value) {
            await api.put(`/admin/users/${editingId.value}`, payload)
            toast.add({ severity: 'success', summary: 'Успех', detail: 'Пользователь обновлен', life: 3000 })
        } else {
            await api.post('/admin/users', payload)
            toast.add({ severity: 'success', summary: 'Успех', detail: 'Пользователь создан', life: 3000 })
        }
        dialogVisible.value = false
        fetchUsers()
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: error.response?.data?.message || 'Не удалось сохранить', life: 3000 })
    }
}

const deleteUser = async (user) => {
    if (confirm(`Удалить пользователя "${user.name}"?`)) {
        try {
            await api.delete(`/admin/users/${user.id}`)
            toast.add({ severity: 'success', summary: 'Успех', detail: 'Пользователь удален', life: 3000 })
            fetchUsers()
        } catch (error) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось удалить', life: 3000 })
        }
    }
}

const getRandomColor = (id) => {
    const colors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899']
    return colors[id % colors.length]
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('ru-RU')
}

onMounted(fetchUsers)
</script>
