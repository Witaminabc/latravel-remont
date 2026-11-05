import { chatConfig } from './config'
import ChatLayout from './components/ChatLayout.vue'
import ThreadList from './components/ThreadList.vue'
import MessageList from './components/MessageList.vue'
import Composer from './components/Composer.vue'
import StatsCards from './components/StatsCards.vue'
import './chat.css'

const components = { ChatLayout, ThreadList, MessageList, Composer, StatsCards }

/**
 * Vue-плагин пакета teo/chat.
 * Подключение:  import Chat from '@chat';  app.use(Chat, { apiPrefix, authEndpoint, echo })
 */
export default {
    install(app, options = {}) {
        Object.entries(components).forEach(([name, component]) => {
            app.component(name, component)
        })

        Object.assign(chatConfig, options)
        app.provide('chat', chatConfig)
    },
}

export { chatConfig }
export { useChatStore } from './stores/chatStore'
export { default as chatHttp } from './http'
export { ChatLayout, ThreadList, MessageList, Composer, StatsCards }