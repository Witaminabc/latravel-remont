<template>
    <Dialog
        :visible="visible"
        header="Медиатека"
        :modal="true"
        :style="{ width: '70vw' }"
        @update:visible="(val) => emit('update:visible', val)"
    >
        <div v-if="loading" class="p-4 text-center">Загрузка…</div>
        <div v-else class="acf-media-grid">
            <div v-for="file in files" :key="file.id || file.url" class="acf-media-item" @click="choose(file)">
                <img :src="file.url" :alt="file.name" />
                <div class="acf-media-name">{{ file.name }}</div>
            </div>
            <div v-if="!files.length" class="p-4 text-500">Файлы не найдены</div>
        </div>
    </Dialog>
</template>

<script setup>
import { ref, inject, watch } from 'vue'
import axios from 'axios'
import Dialog from 'primevue/dialog'

const props = defineProps({
    visible: { type: Boolean, default: false },
})
const emit = defineEmits(['update:visible', 'select'])

const acf = inject('acf', { mediaEndpoint: '/api/admin/media' })

const files = ref([])
const loading = ref(false)

async function load() {
    loading.value = true
    try {
        const token = localStorage.getItem('token')
        const { data } = await axios.get(acf.mediaEndpoint ?? '/api/admin/media', {
            headers: token ? { Authorization: `Bearer ${token}` } : {},
        })
        files.value = Array.isArray(data) ? data : (data.data ?? [])
    } catch (e) {
        files.value = []
    } finally {
        loading.value = false
    }
}

watch(() => props.visible, (val) => {
    if (val) load()
})

function choose(file) {
    emit('select', file.url)
}
</script>
