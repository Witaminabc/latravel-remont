<template>
    <div class="admin-layout flex">
        <!-- Sidebar -->
        <aside class="admin-sidebar" :class="{ 'collapsed': sidebarCollapsed }">
            <div class="sidebar-header">
<!--                <img src="/images/logo.svg" alt="Logo" class="logo" v-if="!sidebarCollapsed">-->
                <i class="pi pi-bars menu-toggle" @click="toggleSidebar"></i>
            </div>

            <div class="sidebar-menu">
                <div class="menu-item" :class="{ 'active': $route.name === 'dashboard' }" @click="$router.push('/admin')">
                    <i class="pi pi-home"></i>
                    <span v-if="!sidebarCollapsed">Дашборд</span>
                </div>

                <div class="menu-item" :class="{ 'active': $route.path.includes('/pages') }" @click="$router.push('/admin/pages')">
                    <i class="pi pi-file"></i>
                    <span v-if="!sidebarCollapsed">Страницы</span>
                </div>

                <div class="menu-item" :class="{ 'active': $route.path.includes('/posts') }" @click="$router.push('/admin/posts')">
                    <i class="pi pi-article"></i>
                    <span v-if="!sidebarCollapsed">Записи</span>
                </div>

                <div class="menu-item" :class="{ 'active': $route.path.includes('/media') }" @click="$router.push('/admin/media')">
                    <i class="pi pi-image"></i>
                    <span v-if="!sidebarCollapsed">Медиа</span>
                </div>

                <div class="menu-item" :class="{ 'active': $route.path.includes('/taxonomies') }" @click="$router.push('/admin/taxonomies')">
                    <i class="pi pi-tags"></i>
                    <span v-if="!sidebarCollapsed">Категории</span>
                </div>

                <div class="menu-item" :class="{ 'active': $route.path.includes('/users') }" @click="$router.push('/admin/users')">
                    <i class="pi pi-users"></i>
                    <span v-if="!sidebarCollapsed">Пользователи</span>
                </div>

                <div class="menu-item" :class="{ 'active': $route.path.includes('/chat') }" @click="$router.push('/admin/chat')">
                    <i class="pi pi-comments"></i>
                    <span v-if="!sidebarCollapsed">Сообщения</span>
                    <span v-if="!sidebarCollapsed && chatUnread > 0" class="menu-badge">
                        {{ chatUnread > 99 ? '99+' : chatUnread }}
                    </span>
                </div>

                <div class="menu-item" :class="{ 'active': $route.path.includes('/leads') }" @click="$router.push('/admin/leads')">
                    <i class="pi pi-inbox"></i>
                    <span v-if="!sidebarCollapsed">Заявки</span>
                </div>

                <div class="menu-item" :class="{ 'active': $route.path.includes('/acf-groups') }" @click="$router.push('/admin/acf-groups')">
                    <i class="pi pi-sliders-h"></i>
                    <span v-if="!sidebarCollapsed">Кастомные поля</span>
                </div>

                <div class="menu-item" :class="{ 'active': $route.path.includes('/settings') }" @click="$router.push('/admin/settings')">
                    <i class="pi pi-cog"></i>
                    <span v-if="!sidebarCollapsed">Настройки</span>
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="menu-item" @click="logout">
                    <i class="pi pi-sign-out"></i>
                    <span v-if="!sidebarCollapsed">Выйти</span>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="admin-main flex-1">
            <div class="main-header">
                <div class="user-info">
                    <Avatar :label="userInitials" class="mr-2" />
                    <span>{{ userName }}</span>
                </div>
            </div>

            <div class="main-content">
                <router-view />
            </div>
        </div>

        <Toast />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import Avatar from 'primevue/avatar'
import Toast from 'primevue/toast'
import axios from 'axios'
import { useChatStore } from '@chat'

const router = useRouter()
const toast = useToast()
const sidebarCollapsed = ref(false)

// Чат: счётчик непрочитанных + realtime-подключение
const chatStore = useChatStore()
const chatUnread = computed(() => chatStore.unreadTotal)

onMounted(() => {
    if (localStorage.getItem('token')) {
        chatStore.init()
    }
})

const userInitials = computed(() => {
    const name = localStorage.getItem('user_name') || 'Admin'
    return name.charAt(0).toUpperCase()
})

const userName = computed(() => {
    return localStorage.getItem('user_name') || 'Administrator'
})

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value
}

const logout = async () => {
    const token = localStorage.getItem('token')
    try {
        await axios.post('/api/logout', {}, {
            headers: { Authorization: `Bearer ${token}` }
        })
    } catch (error) {
        console.error(error)
    } finally {
        chatStore.disconnect()
        localStorage.removeItem('token')
        localStorage.removeItem('user_name')
        await router.push({ name: 'login' })
        toast.add({
            severity: 'success',
            summary: 'Успех',
            detail: 'Вы вышли из системы',
            life: 3000
        })
    }
}
</script>

<style scoped>
.admin-layout {
    min-height: 100vh;
}

.admin-sidebar {
    width: 280px;
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    color: white;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
}

.admin-sidebar.collapsed {
    width: 80px;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    height: 32px;
}

.menu-toggle {
    cursor: pointer;
    font-size: 20px;
    transition: transform 0.3s;
}

.menu-toggle:hover {
    transform: scale(1.1);
}

.sidebar-menu {
    flex: 1;
    padding: 20px 0;
}

.menu-item {
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.3s;
    color: rgba(255, 255, 255, 0.7);
}

.menu-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.menu-item.active {
    background: rgba(59, 130, 246, 0.2);
    border-left: 3px solid #3b82f6;
    color: white;
}

.menu-item i {
    font-size: 20px;
    width: 24px;
}

.menu-badge {
    margin-left: auto;
    background: #3b82f6;
    color: #fff;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 7px;
    min-width: 20px;
    text-align: center;
}

.sidebar-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding: 20px 0;
}

.admin-main {
    margin-left: 280px;
    transition: margin-left 0.3s ease;
}

.admin-sidebar.collapsed + .admin-main {
    margin-left: 80px;
}

.main-header {
    background: white;
    padding: 16px 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: flex-end;
    align-items: center;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.main-content {
    padding: 24px;
}
</style>
