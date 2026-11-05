<template>
    <div class="media-library">

        <div class="flex justify-content-between align-items-center mb-4">
            <h1 class="text-2xl font-bold">Медиатека</h1>
            <div>
                <input
                    type="file"
                    ref="fileInput"
                    multiple
                    accept="image/*"
                    style="display: none"
                    @change="onFileSelect"
                />
                <Button label="Загрузить" icon="pi pi-upload" @click="triggerFileInput" />
            </div>
        </div>

        <div class="grid">
            <div v-for="file in files" :key="file.id" class="col-6 md:col-3 lg:col-2">
                <Card class="media-card">
                    <template #content>
                        <div class="media-preview">
                            <img :src="file.url" :alt="file.name" @click="selectFile(file)" />
                            <div class="media-actions">
                                <Button icon="pi pi-copy" class="p-button-rounded p-button-text" @click="copyUrl(file.url)" />
                                <Button icon="pi pi-trash" class="p-button-rounded p-button-text p-button-danger" @click="deleteFile(file)" />
                            </div>
                        </div>
                        <div class="media-info">
                            <div class="media-name">{{ file.name }}</div>
                            <div class="media-size">{{ formatSize(file.size) }}</div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <Dialog header="Выберите изображение" v-model:visible="dialogVisible" :modal="true" :style="{ width: '90vw' }">
            <div class="grid">
                <div v-for="file in files" :key="file.id" class="col-4 md:col-3 lg:col-2">
                    <img :src="file.url" class="w-full cursor-pointer" @click="insertImage(file.url)" />
                </div>
            </div>
        </Dialog>
    </div>

</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import api from '../services/api'

const toast = useToast()
const files = ref([])
const dialogVisible = ref(false)
const fileInput = ref(null)
const uploading = ref(false)

const fetchFiles = async () => {
    try {
        const response = await api.get('/admin/media')
        files.value = response.data
    } catch (error) {
        console.error('Error fetching files:', error)
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось загрузить файлы', life: 3000 })
    }
}

const triggerFileInput = () => {
    fileInput.value?.click()
}

const onFileSelect = async (event) => {
    const selectedFiles = Array.from(event.target.files)
    if (selectedFiles.length === 0) return

    uploading.value = true

    for (const file of selectedFiles) {
        await uploadFile(file)
    }

    // Очищаем input, чтобы можно было загружать те же файлы снова
    if (fileInput.value) {
        fileInput.value.value = ''
    }

    uploading.value = false
    fetchFiles()
}

const uploadFile = async (file) => {
    const formData = new FormData()
    formData.append('file', file)

    try {
        const response = await api.post('/admin/media/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        toast.add({
            severity: 'success',
            summary: 'Успех',
            detail: `Файл "${file.name}" загружен`,
            life: 3000
        })

        return response.data
    } catch (error) {
        console.error('Upload error:', error)
        toast.add({
            severity: 'error',
            summary: 'Ошибка',
            detail: `Не удалось загрузить "${file.name}"`,
            life: 3000
        })
        throw error
    }
}

const deleteFile = async (file) => {
    if (confirm(`Удалить файл "${file.name}"?`)) {
        try {
            console.log(file.name);
            await api.delete(`/admin/media/${file.name}`)
            toast.add({ severity: 'success', summary: 'Успех', detail: 'Файл удален', life: 3000 })
            // fetchFiles()
        } catch (error) {
            console.error('Delete error:', error)
            toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось удалить файл', life: 3000 })
        }
    }
}

const copyUrl = (url) => {
    navigator.clipboard.writeText(url)
    toast.add({ severity: 'success', summary: 'Успех', detail: 'URL скопирован', life: 3000 })
}

const selectFile = (file) => {
    dialogVisible.value = true
}

const insertImage = (url) => {
    // Отправляем событие в редактор
    window.opener?.postMessage({ type: 'insertImage', url }, '*')
    dialogVisible.value = false
}

const formatSize = (bytes) => {
    if (!bytes) return '0 B'
    const k = 1024
    const sizes = ['B', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

onMounted(fetchFiles)
</script>

<style scoped>
.media-card {
    cursor: pointer;
    transition: transform 0.3s;
}

.media-card:hover {
    transform: scale(1.05);
}

.media-preview {
    position: relative;
    height: 150px;
    overflow: hidden;
}

.media-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.media-actions {
    position: absolute;
    top: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
}

.media-preview:hover .media-actions {
    display: flex;
}

.media-info {
    padding: 8px;
    text-align: center;
}

.media-name {
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.media-size {
    font-size: 10px;
    color: #64748b;
}
</style>
