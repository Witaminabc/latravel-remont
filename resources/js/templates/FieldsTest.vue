<template>
    <div class="page page--fields-test">
        <header class="ft-header">
            <h1>{{ page.title }}</h1>
            <p v-if="page.content" class="ft-header__lead" v-html="page.content"></p>
        </header>

        <p v-if="!blocksOn" class="ft-disabled">Блоки шаблона отключены в настройках страницы.</p>

        <template v-else>
            <section class="ft-section">
                <h2>Скалярные поля</h2>

            <dl class="ft-grid">
                <div class="ft-item">
                    <dt>Text (ft_text)</dt>
                    <dd>{{ acf.ft_text ?? '—' }}</dd>
                </div>
                <div class="ft-item">
                    <dt>Textarea (ft_textarea)</dt>
                    <dd class="ft-pre">{{ acf.ft_textarea ?? '—' }}</dd>
                </div>
                <div class="ft-item">
                    <dt>Number (ft_number)</dt>
                    <dd>{{ acf.ft_number ?? '—' }}</dd>
                </div>
                <div class="ft-item">
                    <dt>Select (ft_select)</dt>
                    <dd>{{ acf.ft_select ?? '—' }}</dd>
                </div>
                <div class="ft-item">
                    <dt>Boolean (ft_boolean)</dt>
                    <dd>{{ acf.ft_boolean ? 'Да' : 'Нет' }}</dd>
                </div>
                <div class="ft-item">
                    <dt>Image (ft_image)</dt>
                    <dd>
                        <img v-if="acf.ft_image" class="ft-image" :src="acf.ft_image" alt="" />
                        <span v-else>—</span>
                    </dd>
                </div>
            </dl>
        </section>

        <section class="ft-section">
            <h2>Wysiwyg (ft_wysiwyg)</h2>
            <RichText v-if="acf.ft_wysiwyg" :html="acf.ft_wysiwyg" />
            <p v-else>—</p>
        </section>

        <section class="ft-section">
            <h2>Repeater (ft_items)</h2>

            <p v-if="!items.length">—</p>
            <ol v-else class="ft-repeat">
                <li v-for="(row, index) in items" :key="index" class="ft-repeat__row">
                    <strong>{{ row.title || `#${index + 1}` }}</strong>
                    <span v-if="row.note" class="ft-repeat__note">{{ row.note }}</span>
                    <span class="ft-repeat__meta">вес: {{ row.weight ?? '—' }}, флаг: {{ row.flag ? 'да' : 'нет' }}</span>
                </li>
            </ol>
        </section>
        </template>
    </div>
</template>

<script setup>
/**
 * Демо-шаблон «все типы полей»: проверяет вывод каждой сущности teo/acf.
 */
import { computed } from 'vue'
import RichText from './blocks/RichText.vue'

const props = defineProps({
    page: { type: Object, required: true },
    acf: { type: Object, default: () => ({}) },
    seo: { type: Object, default: () => ({}) },
    settings: { type: Object, default: () => ({}) },
    template: { type: String, default: '' },
})

const items = computed(() => (Array.isArray(props.acf?.ft_items) ? props.acf.ft_items : []))

// Выключение блока в админке: meta.blocks_enabled.group_fields_test
const blocksOn = computed(() => props.page?.meta?.blocks_enabled?.group_fields_test !== false)
</script>

<style scoped>
.page--fields-test {
    max-width: 860px;
    margin: 0 auto;
    padding: 32px 20px 64px;
}

.ft-header h1 {
    margin: 0 0 12px;
    font-size: 2.2rem;
}

.ft-header__lead {
    font-size: 1.05rem;
    line-height: 1.6;
}

.ft-disabled {
    margin-top: 24px;
    padding: 16px;
    background: #f3f4f6;
    border-radius: 8px;
    color: #6b7280;
}

.ft-section {
    margin-top: 32px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
}

.ft-section h2 {
    font-size: 1.2rem;
    margin: 0 0 16px;
}

.ft-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
    margin: 0;
}

.ft-item dt {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
    margin-bottom: 4px;
}

.ft-item dd {
    margin: 0;
    font-size: 1rem;
}

.ft-pre {
    white-space: pre-wrap;
}

.ft-image {
    max-width: 240px;
    border-radius: 8px;
    display: block;
}

.ft-repeat {
    margin: 0;
    padding-left: 20px;
    display: grid;
    gap: 10px;
}

.ft-repeat__row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 16px;
    align-items: baseline;
}

.ft-repeat__note {
    color: #374151;
}

.ft-repeat__meta {
    font-size: 0.85rem;
    color: #6b7280;
}
</style>