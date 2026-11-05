<template>
    <div ref="scroller" class="teo-chat-messages" @scroll="onScroll">
        <div class="teo-chat-messages__more">
            <Button
                v-if="hasMore"
                label="Показать ранее"
                link
                size="small"
                :loading="loading"
                @click="loadOlder"
            />
            <span v-else-if="loading" class="teo-chat-messages__loading">
                Загрузка…
            </span>
        </div>

        <div v-if="!messages.length && !loading" class="teo-chat-empty teo-chat-empty--messages">
            Сообщений пока нет — напишите первым
        </div>

        <template v-for="group in grouped" :key="group.day">
            <div class="teo-chat-day">
                <span>{{ group.label }}</span>
            </div>

            <div
                v-for="m in group.items"
                :key="m.id"
                class="teo-msg"
                :class="{
                    'is-own': isOwn(m),
                    'is-pending': m.pending,
                    'is-failed': m.failed,
                }"
            >
                <div class="teo-msg__bubble">
                    <span class="teo-msg__text">{{ m.message }}</span>
                    <span class="teo-msg__meta">
                        <span v-if="m.pending" class="teo-msg__status">
                            <i class="pi pi-spinner pi-spin"></i>
                        </span>
                        <button
                            v-else-if="m.failed"
                            type="button"
                            class="teo-msg__status is-failed"
                            title="Не отправлено — нажмите для повтора"
                            @click="$emit('retry', m)"
                        >
                            <i class="pi pi-exclamation-circle"></i>
                        </button>
                        <span
                            v-else-if="isOwn(m)"
                            class="teo-msg__ticks"
                            :class="{ 'is-read': m.is_read }"
                        >✓✓</span>
                        <span class="teo-msg__time">{{ formatMessageTime(m.created_at) }}</span>
                    </span>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import Button from 'primevue/button'
import { dayKey, formatDayLabel, formatMessageTime } from '../utils'

const props = defineProps({
    messages: { type: Array, default: () => [] },
    meId: { type: Number, default: null },
    loading: { type: Boolean, default: false },
    hasMore: { type: Boolean, default: false },
})

const emit = defineEmits(['load-older', 'retry'])

const scroller = ref(null)
const pinned = ref(true)
let prevHeight = 0
let prevTop = 0

const grouped = computed(() => {
    const groups = []
    let current = null

    for (const m of props.messages) {
        const key = m.created_at ? dayKey(m.created_at) : 'unknown'
        if (!current || current.day !== key) {
            current = {
                day: key,
                label: m.created_at ? formatDayLabel(m.created_at) : '',
                items: [],
            }
            groups.push(current)
        }
        current.items.push(m)
    }

    return groups
})

function isOwn(m) {
    return m.from_user?.id === props.meId
}

function onScroll() {
    const el = scroller.value
    if (!el) return

    pinned.value = el.scrollHeight - el.scrollTop - el.clientHeight < 80

    if (el.scrollTop < 40 && props.hasMore && !props.loading) {
        loadOlder()
    }
}

function loadOlder() {
    if (!props.hasMore || props.loading) return

    const el = scroller.value
    prevHeight = el?.scrollHeight || 0
    prevTop = el?.scrollTop || 0
    emit('load-older')
}

async function scrollBottom() {
    await nextTick()
    const el = scroller.value
    if (el) el.scrollTop = el.scrollHeight
}

onMounted(scrollBottom)

// Новые сообщения: доскроллить вниз, если пользователь прибит к низу
watch(() => props.messages.length, async (length, oldLength) => {
    if (!oldLength || (length > oldLength && pinned.value)) {
        await scrollBottom()
    }
})

// Подгрузка завершилась: восстановить позицию прокрутки
watch(() => props.loading, async (loading, was) => {
    if (was && !loading && prevHeight) {
        await nextTick()
        const el = scroller.value
        if (el) el.scrollTop = el.scrollHeight - prevHeight + prevTop
        prevHeight = 0
    }
})
</script>
