<template>
    <div class="teo-chat-stats">
        <div
            v-for="item in items"
            :key="item.label"
            class="teo-chat-stat"
            :class="item.cls"
        >
            <span class="teo-chat-stat__value">{{ item.value }}</span>
            <span class="teo-chat-stat__label">{{ item.label }}</span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
})

const items = computed(() => [
    { label: 'Диалогов', value: props.stats.threads ?? 0, cls: '' },
    { label: 'Пользователей', value: props.stats.total_users ?? 0, cls: '' },
    { label: 'Онлайн', value: props.stats.online_users ?? 0, cls: 'is-online' },
    {
        label: 'Непрочитанных',
        value: props.stats.unread_messages ?? 0,
        cls: props.stats.unread_messages > 0 ? 'is-unread' : '',
    },
    { label: 'Сообщений', value: props.stats.total_messages ?? 0, cls: '' },
])
</script>
