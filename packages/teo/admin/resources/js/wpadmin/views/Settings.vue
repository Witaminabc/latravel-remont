<template>
    <div class="settings">
        <h1 class="text-2xl font-bold mb-4">Настройки сайта</h1>

        <!-- Общие настройки -->
        <Card>
            <template #title>Основные настройки</template>
            <template #content>
                <div class="field mb-3">
                    <label class="font-bold block mb-2">Название сайта</label>
                    <InputText v-model="settings.site_name" class="w-full" />
                </div>
                <div class="field mb-3">
                    <label class="font-bold block mb-2">Описание сайта</label>
                    <Textarea v-model="settings.site_description" rows="3" class="w-full" />
                </div>
                <div class="field mb-3">
                    <label class="font-bold block mb-2">Ключевые слова</label>
                    <InputText v-model="settings.site_keywords" class="w-full" />
                </div>
                <div class="field mb-3">
                    <label class="font-bold block mb-2">Логотип</label>
                    <div v-if="settings.site_logo" class="mb-2">
                        <img :src="settings.site_logo" class="max-h-20" />
                    </div>
                    <FileUpload name="logo" url="/api/admin/settings/logo" @upload="onLogoUpload" :auto="true" accept="image/*" />
                </div>
                <div class="field mb-3">
                    <label class="font-bold block mb-2">Favicon</label>
                    <FileUpload name="favicon" url="/api/admin/settings/logo" @upload="onFaviconUpload" :auto="true" accept="image/*" />
                </div>
            </template>
        </Card>

        <!-- Контакты -->
        <Card class="mt-4">
            <template #title>Контактные данные</template>
            <template #content>
                <div class="field mb-3">
                    <label class="font-bold block mb-2">Email для связи</label>
                    <InputText v-model="settings.contact_email" class="w-full" />
                </div>
                <div class="field mb-3">
                    <label class="font-bold block mb-2">Телефон</label>
                    <InputText v-model="settings.contact_phone" class="w-full" />
                </div>
                <div class="field mb-3">
                    <label class="font-bold block mb-2">Адрес</label>
                    <Textarea v-model="settings.contact_address" rows="2" class="w-full" />
                </div>
            </template>
        </Card>

        <!-- Мессенджеры и соцсети для шапки -->
        <Card class="mt-4">
            <template #title>Мессенджеры и соцсети</template>
            <template #content>
                <div class="grid">
                    <div v-for="network in primarySocials" :key="network.key" class="col-6">
                        <label class="font-bold block mb-2">{{ network.label }}</label>
                        <div class="social-field">
                            <span class="social-field__icon" :style="{ '--social-color': network.color }">
                                <img
                                    v-if="socialLogo(network.key)"
                                    class="social-field__logo"
                                    :src="socialLogo(network.key)"
                                    :alt="network.label"
                                >
                                <svg v-else viewBox="0 0 24 24" aria-hidden="true">
                                    <path v-for="(path, index) in network.paths" :key="index" :d="path"></path>
                                </svg>
                            </span>
                            <InputText
                                v-model="settings[network.setting]"
                                class="w-full"
                                :placeholder="network.placeholder"
                            />
                        </div>
                        <div class="social-logo-actions">
                            <label class="social-logo-upload">
                                <input
                                    type="file"
                                    accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                    :disabled="uploadingLogo === network.key"
                                    @change="onSocialLogoUpload($event, network.key)"
                                >
                                <i class="pi pi-upload"></i>
                                <span>{{ uploadingLogo === network.key ? 'Загрузка…' : 'Свой логотип' }}</span>
                            </label>
                            <Button
                                v-if="socialLogo(network.key)"
                                label="Сбросить"
                                icon="pi pi-times"
                                class="p-button-text p-button-danger p-button-sm"
                                @click="removeSocialLogo(network.key)"
                            />
                            <small v-else class="text-color-secondary">По умолчанию — брендовая иконка</small>
                        </div>
                    </div>
                </div>
                <small class="block mt-2 text-color-secondary">
                    Заполненные сети выводятся иконками в правой части шапки сайта.
                </small>
            </template>
        </Card>

        <!-- Прочие социальные сети -->
        <Card class="mt-4">
            <template #title>Другие сети</template>
            <template #content>
                <div class="grid">
                    <div class="col-6">
                        <label class="font-bold block mb-2">Facebook</label>
                        <InputText v-model="settings.social_facebook" class="w-full" />
                    </div>
                    <div class="col-6">
                        <label class="font-bold block mb-2">Twitter</label>
                        <InputText v-model="settings.social_twitter" class="w-full" />
                    </div>
                    <div class="col-6">
                        <label class="font-bold block mb-2">Instagram</label>
                        <InputText v-model="settings.social_instagram" class="w-full" />
                    </div>
                    <div class="col-6">
                        <label class="font-bold block mb-2">YouTube</label>
                        <InputText v-model="settings.social_youtube" class="w-full" />
                    </div>
                    <div class="col-6">
                        <label class="font-bold block mb-2">GitHub</label>
                        <InputText v-model="settings.social_github" class="w-full" />
                    </div>
                </div>
            </template>
        </Card>

        <!-- Системные настройки -->
        <Card class="mt-4">
            <template #title>Системные параметры</template>
            <template #content>
                <div class="field mb-3">
                    <label class="font-bold block mb-2">Элементов на странице (админка)</label>
                    <InputNumber v-model="settings.items_per_page" class="w-full" />
                </div>
                <div class="field-checkbox mb-3">
                    <Checkbox v-model="settings.maintenance_mode" :binary="true" />
                    <label class="ml-2">Режим обслуживания</label>
                </div>
                <div class="field-checkbox mb-3">
                    <Checkbox v-model="settings.allow_registration" :binary="true" />
                    <label class="ml-2">Разрешить регистрацию</label>
                </div>
                <div class="field mb-3">
                    <label class="font-bold block mb-2">Роль по умолчанию</label>
                    <Dropdown v-model="settings.default_user_role" :options="['user', 'editor']" class="w-full" />
                </div>
            </template>
        </Card>

        <!-- Email настройки (SMTP) -->
        <Card class="mt-4">
            <template #title>SMTP настройки (почта)</template>
            <template #content>
                <div class="grid">
                    <div class="col-6">
                        <label class="font-bold block mb-2">SMTP Host</label>
                        <InputText v-model="settings.smtp_host" class="w-full" />
                    </div>
                    <div class="col-6">
                        <label class="font-bold block mb-2">SMTP Port</label>
                        <InputNumber v-model="settings.smtp_port" class="w-full" />
                    </div>
                    <div class="col-6">
                        <label class="font-bold block mb-2">Encryption</label>
                        <Dropdown v-model="settings.smtp_encryption" :options="['tls', 'ssl']" class="w-full" />
                    </div>
                    <div class="col-6">
                        <label class="font-bold block mb-2">SMTP Username</label>
                        <InputText v-model="settings.smtp_username" class="w-full" />
                    </div>
                    <div class="col-12">
                        <label class="font-bold block mb-2">SMTP Password</label>
                        <InputText type="password" v-model="settings.smtp_password" class="w-full" />
                    </div>
                </div>
            </template>
        </Card>

        <!-- SEO настройки (метрики, коды) -->
        <Card class="mt-4">
            <template #title>SEO и аналитика</template>
            <template #content>
                <div class="field mb-3">
                    <label class="font-bold block mb-2">Google Analytics ID</label>
                    <InputText v-model="settings.google_analytics_id" class="w-full" />
                </div>
                <div class="field mb-3">
                    <label class="font-bold block mb-2">Яндекс.Метрика ID</label>
                    <InputText v-model="settings.yandex_metrika_id" class="w-full" />
                </div>
                <div class="field mb-3">
                    <label class="font-bold block mb-2">Код в <code>&lt;head&gt;</code></label>
                    <Textarea v-model="settings.custom_head_code" rows="4" class="w-full font-mono" />
                </div>
                <div class="field mb-3">
                    <label class="font-bold block mb-2">Код в начале <code>&lt;body&gt;</code></label>
                    <Textarea v-model="settings.custom_body_code" rows="4" class="w-full font-mono" />
                </div>
            </template>
        </Card>

        <!-- Меню сайта (шапка) -->
        <Card class="mt-4">
            <template #title>Меню сайта (шапка)</template>
            <template #content>
                <p class="mt-0 mb-3 text-color-secondary">
                    Подпункты выводятся выпадающим меню в шапке. Если в меню остались
                    ссылки на несуществующие страницы, шапка автоматически строит
                    меню по реальной иерархии опубликованных страниц.
                </p>

                <p v-if="!menuItems.length" class="mt-0 mb-3 text-color-secondary">
                    Пунктов пока нет — будет показано автоматическое меню из опубликованных страниц.
                </p>

                <div v-for="(item, index) in menuItems" :key="`top-${index}`" class="menu-editor">
                    <div class="menu-editor__row">
                        <InputText v-model="item.title" class="w-full" placeholder="Название пункта" />
                        <InputText v-model="item.url" class="w-full" placeholder="/portfolio" />
                        <Button
                            icon="pi pi-arrow-up"
                            class="p-button-text p-button-secondary"
                            :disabled="index === 0"
                            aria-label="Выше"
                            @click="moveMenuItem(index, -1)"
                        />
                        <Button
                            icon="pi pi-arrow-down"
                            class="p-button-text p-button-secondary"
                            :disabled="index === menuItems.length - 1"
                            aria-label="Ниже"
                            @click="moveMenuItem(index, 1)"
                        />
                        <Button
                            icon="pi pi-times"
                            class="p-button-text p-button-danger"
                            aria-label="Удалить"
                            @click="removeMenuItem(index)"
                        />
                    </div>

                    <div
                        v-for="(child, childIndex) in item.children"
                        :key="`child-${index}-${childIndex}`"
                        class="menu-editor__row menu-editor__row--child"
                    >
                        <InputText v-model="child.title" class="w-full" placeholder="Название подпункта" />
                        <InputText v-model="child.url" class="w-full" placeholder="/portfolio/object" />
                        <Button
                            icon="pi pi-arrow-up"
                            class="p-button-text p-button-secondary"
                            :disabled="childIndex === 0"
                            aria-label="Выше"
                            @click="moveChild(index, childIndex, -1)"
                        />
                        <Button
                            icon="pi pi-arrow-down"
                            class="p-button-text p-button-secondary"
                            :disabled="childIndex === item.children.length - 1"
                            aria-label="Ниже"
                            @click="moveChild(index, childIndex, 1)"
                        />
                        <Button
                            icon="pi pi-times"
                            class="p-button-text p-button-danger"
                            aria-label="Удалить подпункт"
                            @click="removeChild(index, childIndex)"
                        />
                    </div>

                    <Button
                        label="Добавить подпункт"
                        icon="pi pi-plus"
                        class="p-button-text p-button-sm menu-editor__add-child"
                        @click="addChild(index)"
                    />
                </div>

                <Button
                    label="Добавить пункт"
                    icon="pi pi-plus"
                    class="p-button-outlined mt-2"
                    @click="addMenuItem"
                />
            </template>
        </Card>

        <div class="mt-4">
            <Button label="Сохранить настройки" icon="pi pi-save" @click="saveSettings" />
            <Button label="Очистить кэш" icon="pi pi-trash" class="p-button-secondary ml-2" @click="clearCache" />
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import Checkbox from 'primevue/checkbox'
import FileUpload from 'primevue/fileupload'
import api from '../services/api'  // используем ваш настроенный api с токеном
import { SOCIAL_ICONS } from '@/public/socialIcons.js'

const toast = useToast()
const settings = ref({})

// Основные мессенджеры шапки: иконка + поле social_<key>
const SOCIAL_PLACEHOLDERS = {
    telegram: 'https://t.me/username',
    max: 'https://max.ru/u/username',
    vk: 'https://vk.com/group',
    whatsapp: 'https://wa.me/79991234567',
}

const primarySocials = ['telegram', 'max', 'vk', 'whatsapp'].map((key) => ({
    key,
    label: SOCIAL_ICONS[key].label,
    color: SOCIAL_ICONS[key].color,
    paths: SOCIAL_ICONS[key].paths,
    setting: `social_${key}`,
    placeholder: SOCIAL_PLACEHOLDERS[key],
}))

// Пункты меню шапки (settings.menu_items), редактируются списком
// Кастомный логотип соцсети: значение в settings — путь в storage, в превью — URL
const uploadingLogo = ref('')

const socialLogo = (key) => {
    const value = settings.value[`social_${key}_logo`]
    if (!value || typeof value !== 'string') return ''

    if (/^https?:\/\//i.test(value) || value.startsWith('/')) return value

    return `/storage/${value.replace(/^\/+/, '')}`
}

const onSocialLogoUpload = async (event, key) => {
    const file = event.target.files?.[0]
    if (!file) return

    const formData = new FormData()
    formData.append('network', key)
    formData.append('logo', file)

    uploadingLogo.value = key

    try {
        const { data } = await api.post('/admin/settings/social-logo', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        settings.value[`social_${key}_logo`] = data.path
        toast.add({ severity: 'success', summary: 'Успех', detail: 'Логотип загружен', life: 3000 })
    } catch (error) {
        console.error(error)
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось загрузить логотип', life: 3000 })
    } finally {
        uploadingLogo.value = ''
        event.target.value = ''
    }
}

const removeSocialLogo = async (key) => {
    try {
        await api.post('/admin/settings/social-logo/remove', { network: key })
        settings.value[`social_${key}_logo`] = ''
        toast.add({ severity: 'success', summary: 'Успех', detail: 'Логотип сброшен', life: 3000 })
    } catch (error) {
        console.error(error)
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось сбросить логотип', life: 3000 })
    }
}

const menuItems = computed(() => Array.isArray(settings.value.menu_items) ? settings.value.menu_items : [])

const addMenuItem = () => {
    menuItems.value.push({ title: '', url: '', order: menuItems.value.length, children: [] })
}

const removeMenuItem = (index) => {
    menuItems.value.splice(index, 1)
}

const moveMenuItem = (index, delta) => {
    const target = index + delta
    if (target < 0 || target >= menuItems.value.length) return

    const [item] = menuItems.value.splice(index, 1)
    menuItems.value.splice(target, 0, item)
}

const addChild = (index) => {
    const item = menuItems.value[index]
    if (!item) return

    if (!Array.isArray(item.children)) item.children = []
    item.children.push({ title: '', url: '', order: item.children.length, children: [] })
}

const removeChild = (itemIndex, childIndex) => {
    const item = menuItems.value[itemIndex]
    if (!item || !Array.isArray(item.children)) return

    item.children.splice(childIndex, 1)
}

const moveChild = (itemIndex, childIndex, delta) => {
    const item = menuItems.value[itemIndex]
    if (!item || !Array.isArray(item.children)) return

    const target = childIndex + delta
    if (target < 0 || target >= item.children.length) return

    const [child] = item.children.splice(childIndex, 1)
    item.children.splice(target, 0, child)
}

/** Приведение пунктов меню (включая подпункты) к редактируемому виду. */
const mapMenuItems = (items) => (Array.isArray(items) ? items : []).map((item, index) => ({
    title: item?.title ?? '',
    url: item?.url ?? '',
    order: Number.isFinite(Number(item?.order)) ? Number(item.order) : index,
    children: mapMenuItems(item?.children),
}))

/** Очистка дерева перед сохранением: пустые пункты не отправляем. */
const cleanMenuTree = (items) => (Array.isArray(items) ? items : [])
    .filter((item) => String(item.title ?? '').trim() !== '' && String(item.url ?? '').trim() !== '')
    .map((item, index) => ({
        title: String(item.title).trim(),
        url: String(item.url).trim(),
        order: index,
        children: cleanMenuTree(item.children),
    }))

const fetchSettings = async () => {
    try {
        const response = await api.get('/admin/settings')
        const data = response.data ?? {}

        // menu_items приходит массивом (с вложенными children)
        data.menu_items = mapMenuItems(data.menu_items)

        settings.value = data
    } catch (error) {
        console.error(error)
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось загрузить настройки', life: 3000 })
    }
}

const saveSettings = async () => {
    try {
        // Пустые строки не отправляем; order пересчитываем по порядку в списке
        settings.value.menu_items = cleanMenuTree(menuItems.value)

        await api.post('/admin/settings', settings.value)
        toast.add({ severity: 'success', summary: 'Успех', detail: 'Настройки сохранены', life: 3000 })
    } catch (error) {
        console.error(error)
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось сохранить настройки', life: 3000 })
    }
}

const clearCache = async () => {
    try {
        await api.post('/admin/settings/cache/clear')
        toast.add({ severity: 'success', summary: 'Успех', detail: 'Кэш очищен', life: 3000 })
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось очистить кэш', life: 3000 })
    }
}

const onLogoUpload = (event) => {
    const uploaded = event.files[0]
    if (uploaded) {
        // После загрузки сервер возвращает путь к файлу (поле path), обновляем settings.site_logo
        const response = event.xhr.response
        if (response) {
            const data = JSON.parse(response)
            settings.value.site_logo = data.path
            toast.add({ severity: 'success', summary: 'Успех', detail: 'Логотип загружен', life: 3000 })
        }
    }
}

const onFaviconUpload = (event) => {
    const uploaded = event.files[0]
    if (uploaded) {
        const response = event.xhr.response
        if (response) {
            const data = JSON.parse(response)
            settings.value.site_favicon = data.path
            toast.add({ severity: 'success', summary: 'Успех', detail: 'Favicon загружен', life: 3000 })
        }
    }
}

onMounted(fetchSettings)
</script>

<style scoped>
.social-field {
    display: flex;
    align-items: center;
    gap: 10px;
}

.social-field__icon {
    display: inline-flex;
    flex: 0 0 34px;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border: 1px solid #e5e7eb;
    border-radius: 50%;
    background: #f8fafc;
    color: var(--social-color, #2563eb);
}

.social-field__icon svg {
    width: 17px;
    height: 17px;
    fill: currentColor;
}

.social-field__logo {
    display: block;
    width: 20px;
    height: 20px;
    object-fit: contain;
}

.social-logo-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    margin-top: 6px;
}

.social-logo-upload {
    display: inline-flex;
    gap: 6px;
    align-items: center;
    color: #2563eb;
    font-size: 0.85rem;
    cursor: pointer;
}

.social-logo-upload:hover {
    text-decoration: underline;
}

.social-logo-upload input {
    display: none;
}

.social-logo-upload:has(input:disabled) {
    opacity: 0.6;
    cursor: default;
    text-decoration: none;
}

.menu-editor {
    margin-bottom: 14px;
    padding-bottom: 10px;
    border-bottom: 1px dashed #e5e7eb;
}

.menu-editor__row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto auto auto;
    gap: 8px;
    align-items: center;
    margin-bottom: 8px;
}

.menu-editor__row--child {
    margin-left: 24px;
}

.menu-editor__add-child {
    margin-left: 24px;
}

@media (max-width: 640px) {
    .menu-editor__row {
        grid-template-columns: 1fr;
    }

    .menu-editor__row--child,
    .menu-editor__add-child {
        margin-left: 12px;
    }
}
</style>
