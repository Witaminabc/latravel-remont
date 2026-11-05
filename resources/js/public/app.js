import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { installSeoSync } from '@pages'
import { resolveTemplateComponent } from '../templates/index.js'
import PublicLayout from './PublicLayout.vue'
import ChatWidget from './ChatWidget.vue'

// Обновление <head> при клиентских переходах (серверная мета — при первой загрузке)
installSeoSync()

// Плавающая кнопка чата 💬 на всех публичных шаблонах (отдельное Vue-приложение)
function mountChatWidget() {
    const host = document.createElement('div')
    document.body.appendChild(host)
    createApp({ render: () => h(ChatWidget) }).mount(host)
}

createInertiaApp({
    resolve (name) {
        const component = resolveTemplateComponent(name)
        if (component) {
            return component
        }

        throw new Error(
            `Не найден шаблон страницы: "${name}". ` +
            'Добавьте компонент в resources/js/templates и зарегистрируйте его в PageTemplates (app/Providers/PageTemplatesProvider.php).'
        )
    },
    setup ({ el, App, props, plugin }) {
        // Шапка сайта — общий layout для всех публичных шаблонов
        createApp({
            render: () => h(PublicLayout, null, { default: () => h(App, props) })
        })
            .use(plugin)
            .mount(el)

        mountChatWidget()
    }
})