<template>
    <div class="teo-chat">
        <StatsCards v-if="store.stats" :stats="store.stats" />

        <div class="teo-chat__body">
            <ThreadList
                v-model:search="store.search"
                :threads="store.filteredThreads"
                :active-id="store.activeUserId"
                :loading="store.loadingThreads"
                @select="onSelect"
            />

            <section class="teo-chat__dialog">
                <div v-if="store.error" class="teo-chat__error">{{ store.error }}</div>

                <div v-if="!peer" class="teo-chat__placeholder">
                    <i class="pi pi-comments"></i>
                    <p>Выберите диалог слева</p>
                </div>

                <template v-else>
                    <header class="teo-chat__header">
                        <span
                            class="teo-thread__avatar"
                            :class="{ 'is-online': peer.is_online }"
                        >{{ initials(peer.name) }}</span>

                        <div class="teo-chat__peer">
                            <strong>{{ peer.name }}</strong>
                            <small
                                class="teo-chat__peer-status"
                                :class="{ 'is-online': peer.is_online }"
                            >
                                {{ peer.is_online ? 'в сети' : 'не в сети' }}<template
                                    v-if="peer.is_blocked"
                                > · заблокирован</template>
                            </small>
                        </div>

                        <div class="teo-chat__actions">
                            <Button
                                :icon="peer.is_blocked ? 'pi pi-lock-open' : 'pi pi-ban'"
                                class="p-button-text"
                                :title="peer.is_blocked ? 'Разблокировать' : 'Заблокировать'"
                                @click="toggleBlock"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-text p-button-danger"
                                title="Очистить историю"
                                @click="clearHistory"
                            />
                        </div>
                    </header>

                    <MessageList
                        :messages="store.activeMessages"
                        :me-id="store.me?.id ?? null"
                        :loading="store.loadingMessages"
                        :has-more="!!store.cursors[store.activeUserId]"
                        @load-older="loadOlder"
                        @retry="onRetry"
                    />

                    <div v-if="peer.is_blocked" class="teo-chat__blocked-note">
                        Пользователь заблокирован — отправка сообщений отключена
                    </div>

                    <Composer
                        :disabled="peer.is_blocked"
                        :sending="store.sending"
                        @send="onSend"
                    />
                </template>
            </section>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue'
import { useToast } from 'primevue/usetoast'
import Button from 'primevue/button'
import ThreadList from './ThreadList.vue'
import MessageList from './MessageList.vue'
import Composer from './Composer.vue'
import StatsCards from './StatsCards.vue'
import { useChatStore } from '../stores/chatStore'
import { initials } from '../utils'

const props = defineProps({
    selectedUserId: { type: [Number, String], default: null },
})

const emit = defineEmits(['select'])

const store = useChatStore()
const toast = useToast()
const peer = computed(() => store.activePeer)

onMounted(async () => {
    await store.init()
    if (props.selectedUserId) {
        await store.openThread(Number(props.selectedUserId))
    }
})

watch(() => props.selectedUserId, async (id) => {
    const num = id ? Number(id) : null
    if (num && num !== store.activeUserId) {
        await store.openThread(num)
    }
})

function onSelect(id) {
    emit('select', id)
}

async function loadOlder() {
    if (store.activeUserId) {
        await store.fetchMessages(store.activeUserId, 'older')
    }
}

function onRetry(message) {
    if (store.activeUserId) {
        store.retryMessage(store.activeUserId, message.id)
    }
}

async function onSend(text) {
    try {
        await store.sendMessage(text)
    } catch {
        toast?.add?.({
            severity: 'error',
            summary: 'Ошибка',
            detail: store.error,
            life: 4000,
        })
    }
}

async function toggleBlock() {
    if (!peer.value) return

    const next = !peer.value.is_blocked
    const text = next
        ? `Заблокировать ${peer.value.name}? Он не сможет писать вам.`
        : `Разблокировать ${peer.value.name}?`
    if (!window.confirm(text)) return

    try {
        await store.toggleBlock(peer.value.id, next)
        toast?.add?.({
            severity: 'success',
            summary: next ? 'Заблокирован' : 'Разблокирован',
            detail: peer.value.name,
            life: 3000,
        })
    } catch {
        toast?.add?.({
            severity: 'error',
            summary: 'Ошибка',
            detail: 'Не удалось изменить блокировку',
            life: 4000,
        })
    }
}

async function clearHistory() {
    if (!peer.value) return
    if (!window.confirm(`Очистить историю диалога с ${peer.value.name}?`)) return

    try {
        await store.clearThread(peer.value.id)
        toast?.add?.({
            severity: 'success',
            summary: 'История очищена',
            life: 2500,
        })
    } catch {
        toast?.add?.({
            severity: 'error',
            summary: 'Ошибка',
            detail: 'Не удалось очистить историю',
            life: 4000,
        })
    }
}
</script>
