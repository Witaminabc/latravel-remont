<template>
    <div class="page page--contacts">
        <template v-if="blocksOn">
            <section class="contacts-hero">
                <h1 class="contacts-hero__title">{{ headingText }}</h1>
                <p v-if="subtitleText" class="contacts-hero__intro">{{ subtitleText }}</p>
            </section>

            <section class="contacts-grid" :class="{ 'contacts-grid--single': !mapSrc }">
                <div class="contacts-card">
                    <h2 class="contacts-card__title">Как нас найти</h2>

                    <ul v-if="hasContactInfo" class="contacts-list">
                        <li v-if="addressText" class="contacts-list__item contacts-list__item--address">
                            <span class="contacts-list__label">Адрес</span>
                            <span class="contacts-list__value">{{ addressText }}</span>
                        </li>
                        <li v-if="phoneText" class="contacts-list__item contacts-list__item--phone">
                            <span class="contacts-list__label">Телефон</span>
                            <span class="contacts-list__value">
                                <a v-if="phoneHref" :href="phoneHref">{{ phoneText }}</a>
                                <template v-else>{{ phoneText }}</template>
                            </span>
                        </li>
                        <li v-if="emailText" class="contacts-list__item contacts-list__item--email">
                            <span class="contacts-list__label">E-mail</span>
                            <span class="contacts-list__value">
                                <a v-if="emailHref" :href="emailHref">{{ emailText }}</a>
                                <template v-else>{{ emailText }}</template>
                            </span>
                        </li>
                        <li v-if="hoursText" class="contacts-list__item contacts-list__item--hours">
                            <span class="contacts-list__label">Режим работы</span>
                            <span class="contacts-list__value">{{ hoursText }}</span>
                        </li>
                        <li v-if="noteText" class="contacts-list__item contacts-list__item--note">
                            <span class="contacts-list__label">Примечание</span>
                            <span class="contacts-list__value">{{ noteText }}</span>
                        </li>
                    </ul>

                    <p v-else class="contacts-card__empty">
                        Контакты пока не заполнены. Добавьте адрес и способы связи в админке.
                    </p>

                    <div class="contacts-card__links">
                        <a
                            v-if="mapLink"
                            class="contacts-card__link contacts-card__link--map"
                            :href="mapLink"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Открыть на карте
                        </a>

                        <a
                            href="/#lead-form"
                            class="contacts-card__link contacts-card__link--form"
                        >
                            Обсудим ваш ремонт
                        </a>
                    </div>
                </div>

                <div v-if="mapSrc" class="contacts-map">
                    <iframe
                        class="contacts-map__frame"
                        :src="mapSrc"
                        :title="mapTitle"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen
                    ></iframe>
                    <p v-if="noteText" class="contacts-map__note">{{ noteText }}</p>
                </div>
            </section>
        </template>

        <section v-if="page.content" class="contacts-content" v-html="page.content"></section>
    </div>
</template>

<script setup>
/**
 * ???????? ?????????.
 * ??????? ??????: settings (????? ?????????) ? ??????????? ??? ACF (group_contacts).
 * ????: ??????.???? ????? iframe (???? map_embed ?? ???????? ??? ?????? ?? ???????????).
 */
import { computed } from 'vue'

const props = defineProps({
    page: { type: Object, required: true },
    acf: { type: Object, default: () => ({}) },
    seo: { type: Object, default: () => ({}) },
    settings: { type: Object, default: () => ({}) },
    template: { type: String, default: '' },
})

const blocksOn = computed(() => props.page?.meta?.blocks_enabled?.group_contacts !== false)

const s = computed(() => props.settings || {})
const a = computed(() => props.acf)

const headingText = computed(() => {
    if (a.value.contacts_heading) return a.value.contacts_heading
    return props.page?.title || 'Контакты'
})
const subtitleText = computed(() => s.value.intro || a.value.contacts_intro || '')

const addressText = computed(() => s.value.address || a.value.contacts_address || '')
const phoneText = computed(() => s.value.phone || a.value.contacts_phone || '')
const emailText = computed(() => s.value.email || a.value.contacts_email || '')
const hoursText = computed(() => s.value.hours || a.value.contacts_hours || '')
const noteText = computed(() => s.value.note || a.value.contacts_note || '')

const phoneHref = computed(() => phoneText.value ? 'tel:' + phoneText.value.replace(/\s/g, '') : null)
const emailHref = computed(() => emailText.value ? 'mailto:' + emailText.value : null)

const hasContactInfo = computed(() => Boolean(addressText.value || phoneText.value || emailText.value || hoursText.value))

const mapLat = computed(() => {
    const v = s.value.map_lat ?? a.value.contacts_map_lat
    return v === null || v === undefined || v === '' ? null : Number(v)
})
const mapLng = computed(() => {
    const v = s.value.map_lng ?? a.value.contacts_map_lng
    return v === null || v === undefined || v === '' ? null : Number(v)
})
const mapZoom = computed(() => {
    const v = s.value.map_zoom ?? a.value.contacts_map_zoom
    return v === null || v === undefined || v === '' ? 16 : Number(v)
})

const embedUrl = computed(() => s.value.map_embed || a.value.contacts_map_embed || '')

const mapSrc = computed(() => {
    if (embedUrl.value) return embedUrl.value
    if (mapLat.value === null || mapLng.value === null) return ''
    return `https://yandex.ru/map-widget/v1/?ll=${mapLng.value}%2C${mapLat.value}&z=${mapZoom.value}&l=map&ol=map&size=600x400`
})

const mapTitle = computed(() => 'Карта: ' + (addressText.value || headingText.value || ''))

const mapLink = computed(() => {
    if (s.value.map_link || a.value.contacts_map_link) return s.value.map_link || a.value.contacts_map_link
    if (mapLat.value === null || mapLng.value === null) return ''
    return `https://yandex.ru/maps/?ll=${mapLng.value}%2C${mapLat.value}&z=${mapZoom.value}`
})
</script>

<style scoped>
.page--contacts {
    max-width: 1120px;
    margin: 0 auto;
    padding: 56px 20px 80px;
}

.contacts-hero {
    margin-bottom: 32px;
    text-align: center;
}

.contacts-hero__title {
    margin: 0 0 12px;
    font-size: 2.2rem;
    line-height: 1.2;
    color: #111827;
}

.contacts-hero__intro {
    margin: 0;
    font-size: 1.15rem;
    color: #4b5563;
    line-height: 1.6;
    max-width: 720px;
    margin-left: auto;
    margin-right: auto;
}

.contacts-grid {
    display: grid;
    grid-template-columns: minmax(300px, 460px) 1fr;
    gap: 28px;
    align-items: start;
}

.contacts-grid--single {
    grid-template-columns: 1fr;
}

.contacts-card {
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    background: #fff;
    padding: 32px;
    box-shadow: 0 16px 40px rgba(17, 24, 39, 0.08);
}

.contacts-card__title {
    margin: 0 0 20px;
    font-size: 1.35rem;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 10px;
}

.contacts-card__title::before {
    content: "\260E";
    color: #2563eb;
}

.contacts-list {
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.contacts-list__item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.contacts-list__label {
    color: #6b7280;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-weight: 600;
}

.contacts-list__value {
    color: #111827;
    font-size: 1rem;
    line-height: 1.5;
    word-break: break-word;
}

.contacts-list__value a {
    color: #2563eb;
    text-decoration: none;
    transition: color 0.15s ease;
}

.contacts-list__value a:hover {
    color: #1d4ed8;
    text-decoration: underline;
}

.contacts-card__empty {
    margin: 0;
    color: #6b7280;
    font-size: 0.95rem;
    line-height: 1.6;
}

.contacts-card__links {
    margin-top: 24px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.contacts-card__link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    transition: all 0.15s ease;
    border: 2px solid transparent;
}

.contacts-card__link--map {
    background: #f3f4f6;
    color: #111827;
    border-color: #e5e7eb;
}

.contacts-card__link--map:hover {
    background: #e5e7eb;
    border-color: #cbd5e1;
    color: #111827;
}

.contacts-card__link--form {
    background: #2563eb;
    color: #fff;
}

.contacts-card__link--form:hover {
    background: #1d4ed8;
}

.contacts-map {
    overflow: hidden;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    background: #f3f4f6;
    min-height: 420px;
    display: flex;
    flex-direction: column;
}

.contacts-map__frame {
    display: block;
    width: 100%;
    height: 420px;
    border: 0;
    flex: 1;
}

.contacts-map__note {
    margin: 0;
    padding: 12px 20px;
    border-top: 1px solid #e5e7eb;
    background: #fff;
    color: #4b5563;
    font-size: 0.9rem;
    line-height: 1.5;
}

.contacts-content {
    margin-top: 40px;
    font-size: 1.05rem;
    line-height: 1.7;
    color: #374151;
}

.contacts-content :deep(img) {
    max-width: 100%;
    height: auto;
}

@media (max-width: 900px) {
    .contacts-grid {
        grid-template-columns: 1fr;
    }

    .contacts-card__title {
        font-size: 1.2rem;
    }
}

@media (max-width: 560px) {
    .page--contacts {
        padding: 40px 16px 56px;
    }

    .contacts-hero__title {
        font-size: 1.6rem;
    }

    .contacts-card {
        padding: 24px;
    }

    .contacts-map__frame {
        height: 300px;
    }
}
</style>
