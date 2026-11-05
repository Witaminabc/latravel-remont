import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'

// Компоненты PrimeVue
import Button from 'primevue/button'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Editor from 'primevue/editor'

// Импорт стилей PrimeVue
import 'primevue/resources/themes/lara-light-blue/theme.css'
import 'primevue/resources/primevue.css'
import 'primeicons/primeicons.css'

// Импорт стилей для сетки PrimeFlex
import 'primeflex/primeflex.css'

// ✅ ВАЖНО: Импорт стилей Quill для редактора
import 'quill/dist/quill.core.css'
import 'quill/dist/quill.snow.css'
import 'quill/dist/quill.bubble.css'

import router from './router'
import App from './App.vue'
import Acf from '@acf'
import Chat from '@chat'

const app = createApp(App)

// Регистрация PrimeVue
app.use(PrimeVue, { ripple: true })
app.use(ToastService)
app.use(ConfirmationService)

// Глобальная регистрация компонентов
app.component('Button', Button)
app.component('Card', Card)
app.component('DataTable', DataTable)
app.component('Column', Column)
app.component('Tag', Tag)
app.component('Editor', Editor)

app.use(createPinia())
app.use(router)

// Плагин кастомных полей teo/acf (регистрирует AcfFieldGroup/AcfField/AcfRepeater + стили)
app.use(Acf, { mediaEndpoint: '/api/admin/media' })

// Плагин чата teo/chat (регистрирует ChatLayout/ThreadList/MessageList/Composer/StatsCards)
app.use(Chat)

app.mount('#app')
