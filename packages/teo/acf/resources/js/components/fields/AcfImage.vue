<template>
    <div class="acf-image">
        <div v-if="model" class="acf-image__preview">
            <img :src="model" alt="" />
            <div class="acf-image__actions">
                <Button type="button" label="Заменить" icon="pi pi-images" class="p-button-text"
                        @click="pickerVisible = true" />
                <Button type="button" label="Удалить" icon="pi pi-trash" class="p-button-text p-button-danger"
                        @click="model = null" />
            </div>
        </div>

        <div v-else class="acf-image__empty">
            <Button type="button" label="Выбрать из медиатеки" icon="pi pi-images" class="p-button-outlined"
                    @click="pickerVisible = true" />
            <FileUpload
                mode="basic"
                name="file"
                :url="uploadUrl"
                :auto="true"
                accept="image/*"
                :headers="uploadHeaders"
                choose-label="Загрузить"
                @upload="onUpload"
            />
        </div>

        <AcfMediaPicker v-model:visible="pickerVisible" @select="onSelect" />
    </div>
</template>

<script setup>
import { ref, computed, inject } from 'vue'
import Button from 'primevue/button'
import FileUpload from 'primevue/fileupload'
import AcfMediaPicker from '../AcfMediaPicker.vue'

defineProps({
    field: { type: Object, required: true },
})

const model = defineModel({ default: null })

const acf = inject('acf', { mediaEndpoint: '/api/admin/media' })
const pickerVisible = ref(false)

const uploadUrl = computed(() => `${acf.mediaEndpoint ?? '/api/admin/media'}/upload`)
const uploadHeaders = computed(() => {
    const token = localStorage.getItem('token')
    return token ? { Authorization: `Bearer ${token}` } : {}
})

function onSelect(url) {
    model.value = url
    pickerVisible.value = false
}

function onUpload(event) {
    try {
        const data = JSON.parse(event.xhr.response)
        if (data.url) model.value = data.url
    } catch (e) {
        /* ignore */
    }
}
</script>
