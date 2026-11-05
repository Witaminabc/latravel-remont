<template>
    <div class="chat-page">
        <div class="flex justify-content-between align-items-center mb-4">
            <h1 class="text-2xl font-bold">Сообщения</h1>
        </div>

        <ChatLayout :selected-user-id="userId" @select="onSelect" />
    </div>
</template>

<script setup>
import { watch } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { useChatStore } from '@chat'

const props = defineProps({
    userId: { type: [String, Number], default: null },
})

const router = useRouter()
const toast = useToast()
const chatStore = useChatStore()

/** Выбран диалог — синхронизируем URL (/admin/chat/{userId}) */
function onSelect(id) {
    if (id) {
        router.push({ name: 'chat.thread', params: { userId: id } })
    } else {
        router.push({ name: 'chat.index' })
    }
}

// Тосты для входящих сообщений, пока вкладка чата не открыта
watch(() => chatStore.notifications.length, (length, previous) => {
    if (length > (previous || 0)) {
        const notification = chatStore.notifications[length - 1]
        if (!notification) return
        // Не показываем тост на свои же сообщения (эхо на мульти-вкладках):
        // в notifications они попадать не должны, но подстрахуемся на UI-уровне.
        const meId = Number(chatStore.me?.id)
        if (meId && Number(notification.from?.id) === meId) return
        toast.add({
            severity: 'info',
            summary: notification.from?.name || 'Сообщение',
            detail: notification.text,
            life: 4000,
        })
    }
})
</script>
