import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '../layouts/MainLayout.vue'

const routes = [
    {
        path: '/admin/login',
        name: 'login',
        component: () => import('../views/Login.vue'),
        meta: { guest: true }
    },
    {
        path: '/admin',
        component: MainLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('../views/Dashboard.vue')
            },
            {
                path: 'pages',
                name: 'pages.index',
                component: () => import('../views/pages/Index.vue')
            },
            {
                path: 'pages/create',
                name: 'pages.create',
                component: () => import('../views/pages/Form.vue')
            },
            {
                path: 'pages/:id/edit',
                name: 'pages.edit',
                component: () => import('../views/pages/Form.vue'),
                props: true
            },
            {
                path: 'posts/create',
                name: 'posts.create',
                component: () => import('../views/pages/Form.vue')
            },
            {
                path: 'posts/:id/edit',
                name: 'posts.edit',
                component: () => import('../views/pages/Form.vue'),
                props: true
            },
            {
                path: 'media',
                name: 'media',
                component: () => import('../views/MediaLibrary.vue')
            },
            {
                path: 'taxonomies',
                name: 'taxonomies',
                component: () => import('../views/Taxonomies.vue')
            },
            {
                path: 'users',
                name: 'users',
                component: () => import('../views/Users.vue')
            },
            {
                path: 'chat',
                name: 'chat.index',
                component: () => import('../views/chat/Index.vue')
            },
            {
                path: 'chat/:userId',
                name: 'chat.thread',
                component: () => import('../views/chat/Index.vue'),
                props: true
            },
            {
                path: 'leads',
                name: 'leads.index',
                component: () => import('../views/leads/Index.vue')
            },
            {
                path: 'settings',
                name: 'settings',
                component: () => import('../views/Settings.vue')
            },
            {
                path: 'acf-groups',
                name: 'acf-groups.index',
                component: () => import('../views/acf/Groups.vue')
            },
            {
                path: 'acf-groups/create',
                name: 'acf-groups.create',
                component: () => import('../views/acf/GroupForm.vue')
            },
            {
                path: 'acf-groups/:key/edit',
                name: 'acf-groups.edit',
                component: () => import('../views/acf/GroupForm.vue'),
                props: true
            },


            {
                path: 'posts',
                name: 'posts.index',
                component: () => import('../views/posts/Index.vue') // ← отдельная компонента
            },

        ]
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: { name: 'dashboard' }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// Guard для проверки авторизации
router.beforeEach(async (to) => {
    const token = localStorage.getItem('token')

    if (to.meta.requiresAuth && !token) {
        return { name: 'login' }
    }

    if (to.meta.guest && token) {
        return { name: 'dashboard' }
    }

    return true
})

export default router
