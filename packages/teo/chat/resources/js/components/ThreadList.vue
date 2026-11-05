<template>
    <aside class="teo-chat-threads">
        <div class="teo-chat-threads__head">
            <InputText
                v-model="search"
                placeholder="Поиск по имени или email"
                class="w-full"
            />
        </div>

        <div class="teo-chat-threads__list">
            <div v-if="loading" class="teo-chat-empty">Загрузка…</div>
            <div v-else-if="!threads.length" class="teo-chat-empty">Диалогов пока нет</div>

            <button
                v-for="t in threads"
                :key="t.user.id"
                type="button"
                class="teo-thread"
                :class="{ 'is-active': t.user.id === activeId }"
                @click="$emit('select', t.user.id)"
            >
                <span
                    class="teo-thread__avatar"
                    :class="{ 'is-online': t.user.is_online }"
                >
                    {{ initials(t.user.name) }}
                </span>

                <span class="teo-thread__body">
                    <span class="teo-thread__line">
                        <span class="teo-thread__name">{{ t.user.name }}</span>
                        <span class="teo-thread__time">
                            {{ formatThreadTime(t.last_message?.created_at) }}
                        </span>
                    </span>
                    <span class="teo-thread__line">
                        <span class="teo-thread__preview">
                            {{ t.last_message?.message || 'Нет сообщений' }}
                        </span>
                        <Badge
                            v-if="t.unread > 0"
                            :value="t.unread > 99 ? '99+' : t.unread"
                            severity="info"
                        />
                        <i
                            v-else-if="t.user.is_blocked"
                            class="pi pi-ban teo-thread__blocked"
                            title="Заблокирован"
                        ></i>
                    </span>
                </span>
            </button>
        </div>
    </aside>
</template>

<script setup>
import InputText from 'primevue/inputtext'
import Badge from 'primevue/badge'
import { initials, formatThreadTime } from '../utils'

defineProps({
    threads: { type: Array, default: () => [] },
    activeId: { type: Number, default: null },
    loading: { type: Boolean, default: false },
})

defineEmits(['select'])

const search = defineModel('search', { type: String, default: '' })
</script>
