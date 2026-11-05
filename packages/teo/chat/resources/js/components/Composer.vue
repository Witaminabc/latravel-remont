<template>
    <div class="teo-chat-composer">
        <Textarea
            ref="input"
            v-model="text"
            :rows="1"
            :disabled="disabled"
            :placeholder="disabled
                ? 'Пользователь заблокирован'
                : 'Введите сообщение… (Enter — отправить, Shift+Enter — новая строка)'"
            class="teo-chat-composer__input"
            @keydown.enter.exact.prevent="submit"
            @input="grow"
        />
        <Button
            icon="pi pi-send"
            :loading="sending"
            :disabled="disabled || !text.trim()"
            aria-label="Отправить"
            @click="submit"
        />
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'

const props = defineProps({
    disabled: { type: Boolean, default: false },
    sending: { type: Boolean, default: false },
})

const emit = defineEmits(['send'])

const text = ref('')
const input = ref(null)

function submit() {
    const value = text.value.trim()
    if (!value || props.disabled || props.sending) return

    emit('send', value)
    text.value = ''
    grow()
}

function grow() {
    const el = input.value?.$el
    if (!el || !el.style) return
    el.style.height = 'auto'
    el.style.height = Math.min(el.scrollHeight, 120) + 'px'
}

watch(() => props.disabled, (disabled) => {
    if (disabled) text.value = ''
})
</script>
