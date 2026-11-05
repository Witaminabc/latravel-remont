<template>
    <section id="quiz" class="quiz-block">
        <div class="quiz-block__inner">
            <h2 class="quiz-block__title">{{ headingText }}</h2>
            <p v-if="leadText" class="quiz-block__lead">{{ leadText }}</p>

            <div class="quiz-block__layout">
                <aside class="quiz-info">
                    <div class="quiz-info__title">{{ bonusesTitleText }}</div>
                    <ul class="quiz-info__items">
                        <li v-for="(bonus, index) in bonusItems" :key="index" class="quiz-info__item">
                            <span class="quiz-info__item-icon">
                                <img v-if="bonus.icon" :src="bonus.icon" alt="" loading="lazy">
                                <span v-else aria-hidden="true">{{ index + 1 }}</span>
                            </span>
                            <span class="quiz-info__item-title">{{ bonus.title }}</span>
                        </li>
                    </ul>
                </aside>

                <div class="quiz-card">
                    <div v-if="screen === 'success'" class="quiz-success">
                        <div class="quiz-success__marker" aria-hidden="true">✓</div>
                        <h3 class="quiz-success__title">{{ successTitleText }}</h3>
                        <p v-if="successTextValue" class="quiz-success__desc">{{ successTextValue }}</p>
                        <button type="button" class="quiz-btn quiz-btn--ghost" @click="restart">
                            {{ L.restart }}
                        </button>
                    </div>

                    <template v-else>
                        <div class="quiz-progress">
                            <div class="quiz-progress__bar">
                                <span :style="{ width: progress + '%' }"></span>
                            </div>
                            <div class="quiz-progress__count">
                                {{ L.step }} {{ stepNumber }} {{ L.of }} {{ totalSteps }}
                            </div>
                        </div>

                        <form v-if="screen === 'contacts'" class="quiz-screen" novalidate @submit.prevent="submit">
                            <div class="quiz-step__head">
                                <span class="quiz-step__marker quiz-step__marker--check" aria-hidden="true">✓</span>
                                <div>
                                    <h3 class="quiz-step__title">{{ contactsTitleText }}</h3>
                                    <p v-if="contactsDescriptionText" class="quiz-step__desc">{{ contactsDescriptionText }}</p>
                                </div>
                            </div>

                            <div class="quiz-fields">
                                <input
                                    v-model="contacts.name"
                                    class="quiz-input"
                                    type="text"
                                    :placeholder="nameLabelText"
                                    autocomplete="name"
                                >
                                <input
                                    v-model="contacts.phone"
                                    class="quiz-input"
                                    type="tel"
                                    :placeholder="phoneLabelText"
                                    autocomplete="tel"
                                >
                                <input
                                    v-model="contacts.email"
                                    class="quiz-input"
                                    type="email"
                                    :placeholder="emailLabelText"
                                    autocomplete="email"
                                >
                            </div>

                            <label class="quiz-consent">
                                <input v-model="contacts.consent" type="checkbox">
                                <span class="quiz-consent__box" aria-hidden="true"></span>
                                <span class="quiz-consent__text">{{ consentPrefix + ' ' }}<a v-if="termsUrlValue" :href="termsUrlValue" target="_blank" rel="noopener noreferrer">Пользовательское соглашение</a><template v-else>Пользовательское соглашение</template> и соглашаюсь с <a v-if="privacyUrlValue" :href="privacyUrlValue" target="_blank" rel="noopener noreferrer">Политикой обработки персональных данных</a><template v-else>Политикой обработки персональных данных</template></span>
                            </label>

                            <p v-if="submitError" class="quiz-error">{{ submitError }}</p>

                            <div class="quiz-buttons">
                                <button type="button" class="quiz-btn quiz-btn--ghost" :disabled="loading" @click="prev">
                                    {{ prevLabelText }}
                                </button>
                                <button type="submit" class="quiz-btn quiz-btn--primary" :disabled="loading">
                                    {{ loading ? sendingTextValue : submitLabelText }}
                                </button>
                            </div>
                        </form>

                        <div v-else class="quiz-screen">
                            <Transition name="quiz-slide" mode="out-in">
                                <div :key="current" class="quiz-step" :class="{ 'quiz-step--media': activeStep.image }">
                                    <div v-if="activeStep.image" class="quiz-step__media">
                                        <img :src="activeStep.image" alt="" loading="lazy">
                                    </div>

                                    <div class="quiz-step__body">
                                        <div class="quiz-step__head">
                                            <span class="quiz-step__marker" aria-hidden="true">{{ current + 1 }}</span>
                                            <div>
                                                <h3 class="quiz-step__title">{{ activeStep.title }}</h3>
                                                <p v-if="activeStep.description" class="quiz-step__desc">{{ activeStep.description }}</p>
                                                <p v-if="activeStep.hint" class="quiz-step__hint">{{ activeStep.hint }}</p>
                                            </div>
                                        </div>

                                        <div class="quiz-options" role="group" :aria-label="activeStep.title">
                                            <label
                                                v-for="option in activeStep.options"
                                                :key="option.label"
                                                class="quiz-option"
                                                :class="{
                                                    'is-selected': isSelected(option.label),
                                                    'quiz-option--checkbox': activeStep.multiple,
                                                }"
                                            >
                                                <input
                                                    :type="activeStep.multiple ? 'checkbox' : 'radio'"
                                                    :name="`quiz-step-${current}`"
                                                    :value="option.label"
                                                    :checked="isSelected(option.label)"
                                                    @change="toggle(option.label)"
                                                >
                                                <span class="quiz-option__marker" aria-hidden="true"></span>
                                                <span class="quiz-option__body">
                                                    <span class="quiz-option__title">{{ option.label }}</span>
                                                    <span v-if="option.description" class="quiz-option__desc">{{ option.description }}</span>
                                                </span>
                                            </label>
                                        </div>

                                        <p v-if="stepError" class="quiz-error">{{ stepError }}</p>
                                    </div>
                                </div>
                            </Transition>

                            <div class="quiz-buttons">
                                <button type="button" class="quiz-btn quiz-btn--ghost" :disabled="current === 0" @click="prev">
                                    {{ prevLabelText }}
                                </button>
                                <button type="button" class="quiz-btn quiz-btn--primary" @click="next">
                                    {{ nextLabelText }}
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
/**
 * Блок «Квиз» главной страницы: пошаговый расчет стоимости.
 *
 * Данные — ACF-группа group_home_quiz: quiz_heading, quiz_lead,
 * quiz_bonuses (repeater), quiz_steps (repeater с вложенным options),
 * тексты шага с контактами, кнопок и экрана «Спасибо».
 * Незаполненный ACF → демо-набор FALLBACK_STEPS (по аналогии с
 * FALLBACK_TARIFFS в Tariffs.vue): значения из админки приоритетнее.
 *
 * Отправка — существующий POST /api/leads (сохраняет заявку и письмо).
 */
import { computed, reactive, ref } from 'vue'
import axios from 'axios'

const props = defineProps({
    heading: { type: String, default: '' },
    lead: { type: String, default: '' },
    bonusesTitle: { type: String, default: '' },
    bonuses: { type: Array, default: () => [] },
    steps: { type: Array, default: () => [] },
    contactsTitle: { type: String, default: '' },
    contactsDescription: { type: String, default: '' },
    nameLabel: { type: String, default: '' },
    phoneLabel: { type: String, default: '' },
    emailLabel: { type: String, default: '' },
    consentText: { type: String, default: '' },
    termsUrl: { type: String, default: '' },
    privacyUrl: { type: String, default: '' },
    nextLabel: { type: String, default: '' },
    prevLabel: { type: String, default: '' },
    submitLabel: { type: String, default: '' },
    errorText: { type: String, default: '' },
    sendingText: { type: String, default: '' },
    submitErrorText: { type: String, default: '' },
    successTitle: { type: String, default: '' },
    successText: { type: String, default: '' },
})

// Статичные подписи интерфейса (не редактируются в ACF).
const L = {
    restart: 'Пройти ещё раз',
    step: 'Шаг',
    of: 'из',
}

const headingText = computed(() => props.heading || 'Узнайте стоимость ремонта ответив на 7 вопросов')
const leadText = computed(() => props.lead || '')
const bonusesTitleText = computed(() => props.bonusesTitle || 'Ваши бонусы в конце теста:')

const contactsTitleText = computed(
    () => props.contactsTitle || 'Ответы приняты, мы уже приступили к расчету'
)
const contactsDescriptionText = computed(
    () => props.contactsDescription
        || 'За вашим номером зафиксирована скидка 5% на ремонт по договору. Оставьте контакты — специалист свяжется с вами и пришлёт расчёт стоимости.'
)

const nameLabelText = computed(() => props.nameLabel || 'Ваше имя')
const phoneLabelText = computed(() => props.phoneLabel || 'Телефон')
const emailLabelText = computed(() => props.emailLabel || 'E-mail (необязательно)')
const consentPrefix = computed(() => props.consentText || 'Прочитал(-а)')
const termsUrlValue = computed(() => props.termsUrl || '')
const privacyUrlValue = computed(() => props.privacyUrl || '')

const nextLabelText = computed(() => props.nextLabel || 'Следующий шаг')
const prevLabelText = computed(() => props.prevLabel || 'Назад')
const submitLabelText = computed(() => props.submitLabel || 'Отправить заявку')
const errorTextValue = computed(() => props.errorText || 'Выберите вариант, чтобы продолжить')
const sendingTextValue = computed(() => props.sendingText || 'Отправка...')
const submitErrorTextValue = computed(
    () => props.submitErrorText || 'Не удалось отправить заявку. Попробуйте еще раз.'
)

const successTitleText = computed(() => props.successTitle || 'Ваша заявка успешно отправлена!')
const successTextValue = computed(
    () => props.successText
        || 'Спасибо! Мы уже приступили к расчёту — специалист свяжется с вами в течение 15 минут.'
)

// Демо-данные: квиз со страницы-источника rembrigada116.ru.
const FALLBACK_BONUSES = [
    { title: 'Расчет стоимости и скидка', icon: '' },
    { title: '1 из 3 подарков на выбор', icon: '' },
]

const FALLBACK_STEPS = [
    {
        title: 'В каком состоянии сейчас находится ваша квартира?',
        description: '',
        hint: '',
        image: '',
        multiple: false,
        options: [
            {
                label: 'Новостройка с предчистовой отделкой (White Box)',
                description: 'стены выровнены, есть базовая стяжка от застройщика',
            },
            {
                label: 'Новостройка без отделки (Черновой кирпич/бетон)',
                description: 'голые стены, нет стяжки и проводки',
            },
            {
                label: 'Вторичное жилье/Хрущевка',
                description: 'требуется полный демонтаж старых покрытий и капитальный ремонт',
            },
        ],
    },
    {
        title: 'Укажите тип дома',
        description: '',
        hint: '',
        image: '',
        multiple: false,
        options: [
            { label: 'Панельный дом / Ленинградка (1970 — 2010 г.)', description: '' },
            { label: 'Хрущевка / Старая пятиэтажка (до 1980 г.)', description: '' },
            { label: 'Сталинка / Старый кирпичный фонд', description: '' },
            { label: 'Современный фонд / Свежий ЖК (с 2010 года)', description: '' },
        ],
    },
    {
        title: 'Какие капитальные работы необходимо выполнить?',
        description: '',
        hint: 'выберите один или несколько вариантов',
        image: '',
        multiple: true,
        options: [
            {
                label: 'Полный демонтаж',
                description: 'снос деревянных полов, старых перегородок, очистка стен от обоев/краски, вынос мусора',
            },
            {
                label: 'Полная замена проводки',
                description: 'старый алюминий на медный кабель по ГОСТ + гарантия 5 лет',
            },
            {
                label: 'Замена труб и сантехники',
                description: 'демонтаж старого чугуна/металла, разводка новых труб REHAU/полипропилен',
            },
            {
                label: 'Выравнивание геометрии',
                description: 'штукатурка стен по маякам под 90 градусов, под кухонный гарнитур и мебель',
            },
        ],
    },
    {
        title: 'Укажите тип планировки и примерную площадь вашей квартиры:',
        description: '',
        hint: '',
        image: '',
        multiple: false,
        options: [
            { label: 'Студия (от 25 до 30 м²)', description: '' },
            { label: '1-комнатная квартира (от 30 до 45 м²)', description: '' },
            { label: '2-комнатная квартира (от 45 до 65 м²)', description: '' },
            { label: '3-комнатная квартира (от 60 до 90+ м²)', description: '' },
        ],
    },
    {
        title: 'У вас есть уже готовый дизайн-проект?',
        description: '',
        hint: '',
        image: '',
        multiple: false,
        options: [
            {
                label: 'Нет, хочу ремонт без переплаты за дизайн',
                description: 'сэкономим до 150 000 руб. и 1,5 месяца времени, сделаем красиво по вашим пожеланиям',
            },
            {
                label: 'Да, есть готовый проект',
                description: 'необходима точная техническая реализация строго по чертежам',
            },
            {
                label: 'Проекта нет',
                description: 'перед началом работ хотел бы заказать планировочное решение',
            },
        ],
    },
    {
        title: 'Какие инженерные работы необходимо выполнить в квартире?',
        description: '',
        hint: 'выберите один или несколько вариантов',
        image: '',
        multiple: true,
        options: [
            {
                label: 'Добавление и перенос розеток',
                description: 'локально под кухонный гарнитур, ТВ-зону и расстановку мебели',
            },
            {
                label: 'Полная замена электрики',
                description: 'монтаж новой надежной проводки с нуля во всей квартире + гарантия 5 лет',
            },
            {
                label: 'Модернизация электрощитка',
                description: 'установка УЗО против ударов током и реле контроля от скачков напряжения',
            },
            {
                label: 'Защита от протечек воды',
                description: 'монтаж автоматической системы Neptun или Gidrolok',
            },
            {
                label: 'Теплый пол',
                description: 'разводка труб водоснабжения',
            },
        ],
    },
    {
        title: 'Когда планируете запускать мастеров на объект?',
        description: '',
        hint: '',
        image: '',
        multiple: false,
        options: [
            {
                label: 'Ключи на руках, готовы начать работы в течение недели',
                description: '',
            },
            {
                label: 'Ключи получаем в течение ближайшего месяца, подбираем надежную бригаду',
                description: '',
            },
            {
                label: 'Дом еще строится / оформляем документы, прицениваемся заранее',
                description: 'на перспективу от 2 до 6 месяцев',
            },
            {
                label: 'Более длительный срок',
                description: '',
            },
        ],
    },
    {
        title: 'Какой подарок закрепить за вашим номером телефона при расчете стоимости?',
        description: '',
        hint: '',
        image: '',
        multiple: false,
        options: [
            { label: 'Кондиционер сплит-система', description: '' },
            {
                label: 'Профессиональный финишный клининг квартиры после ремонта',
                description: '',
            },
            {
                label: 'Надежный робот-пылесос для поддержания чистоты',
                description: '',
            },
        ],
    },

]

// Нормализация ACF: отбрасываем пустые шаги/варианты, приводим типы.
function normalizeOption(option) {
    if (typeof option === 'string') {
        const label = option.trim()
        return label ? { label, description: '' } : null
    }
    if (!option || typeof option !== 'object') return null
    const label = String(option.label ?? option.title ?? '').trim()
    if (!label) return null
    return { label, description: String(option.description ?? '').trim() }
}

function normalizeStep(step) {
    if (!step || typeof step !== 'object') return null
    const title = String(step.title ?? '').trim()
    if (!title) return null

    const options = (Array.isArray(step.options) ? step.options : [])
        .map(normalizeOption)
        .filter(Boolean)

    if (!options.length) return null

    return {
        title,
        description: String(step.description ?? '').trim(),
        hint: String(step.hint ?? '').trim(),
        image: step.image || '',
        multiple: step.multiple === true || step.multiple === 1 || step.multiple === '1',
        options,
    }
}

// Шаги: данные из админки приоритетнее демо-набора.
const normalizedSteps = computed(() => {
    const items = (Array.isArray(props.steps) ? props.steps : [])
        .map(normalizeStep)
        .filter(Boolean)

    return items.length ? items : FALLBACK_STEPS
})

const bonusItems = computed(() => {
    const items = (Array.isArray(props.bonuses) ? props.bonuses : [])
        .map((bonus) => {
            if (!bonus || typeof bonus !== 'object') return null
            const title = String(bonus.title ?? '').trim()
            if (!title) return null
            return { title, icon: bonus.icon || '' }
        })
        .filter(Boolean)

    return items.length ? items : FALLBACK_BONUSES
})

/* Состояние квиза */
const screen = ref('quiz')      // quiz | contacts | success
const current = ref(0)          // индекс текущего вопроса
const answers = ref({})         // { [индекс шага]: [выбранные варианты] }
const stepError = ref('')
const submitError = ref('')
const loading = ref(false)
const contacts = reactive({ name: '', phone: '', email: '', consent: false })

const activeStep = computed(() => normalizedSteps.value[current.value] || normalizedSteps.value[0])
const totalSteps = computed(() => normalizedSteps.value.length + 1)
const stepNumber = computed(() => (screen.value === 'contacts' ? totalSteps.value : current.value + 1))
const progress = computed(() => Math.round((stepNumber.value / totalSteps.value) * 100))

function isSelected(label) {
    return (answers.value[current.value] || []).includes(label)
}

function isStepAnswered(index) {
    return (answers.value[index] || []).length > 0
}

function toggle(label) {
    stepError.value = ''

    const picked = [...(answers.value[current.value] || [])]

    if (activeStep.value.multiple) {
        const position = picked.indexOf(label)
        if (position === -1) picked.push(label)
        else picked.splice(position, 1)
    } else {
        picked.splice(0, picked.length, label)
    }

    answers.value = { ...answers.value, [current.value]: picked }
}

function next() {
    if (!isStepAnswered(current.value)) {
        stepError.value = errorTextValue.value
        return
    }

    stepError.value = ''

    if (current.value < normalizedSteps.value.length - 1) {
        current.value += 1
        return
    }

    screen.value = 'contacts'
}

function prev() {
    stepError.value = ''
    submitError.value = ''

    if (screen.value === 'contacts') {
        screen.value = 'quiz'
        current.value = normalizedSteps.value.length - 1
        return
    }

    if (current.value > 0) current.value -= 1
}

// Ответы уходят в поле message заявки — так они попадут в письмо.
function buildMessage() {
    const lines = []

    normalizedSteps.value.forEach((step, index) => {
        const picked = answers.value[index] || []
        if (picked.length) lines.push(`${step.title} — ${picked.join('; ')}`)
    })

    return lines.length ? `Ответы квиза:\n${lines.join('\n')}` : 'Заявка с квиза'
}

function submit() {
    submitError.value = ''

    if (!contacts.name.trim() || !contacts.phone.trim()) {
        submitError.value = 'Укажите имя и телефон'
        return
    }

    if (!contacts.consent) {
        submitError.value = 'Подтвердите согласие на обработку персональных данных'
        return
    }

    send()
}

async function send() {
    loading.value = true

    try {
        const email = contacts.email.trim()

        await axios.post('/api/leads', {
            name: contacts.name.trim(),
            phone: contacts.phone.trim(),
            email: email || undefined,
            message: buildMessage(),
        })

        screen.value = 'success'
    } catch (e) {
        submitError.value = submitErrorTextValue.value
    } finally {
        loading.value = false
    }
}

function restart() {
    screen.value = 'quiz'
    current.value = 0
    answers.value = {}
    stepError.value = ''
    submitError.value = ''
    contacts.name = ''
    contacts.phone = ''
    contacts.email = ''
    contacts.consent = false
}

</script>

<style scoped>
.quiz-block {
    padding: 56px 20px 80px;
    background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
}

.quiz-block__inner {
    max-width: 1120px;
    margin: 0 auto;
}

.quiz-block__title {
    margin: 0 0 10px;
    text-align: center;
    font-size: 2rem;
    line-height: 1.25;
    color: #111827;
}

.quiz-block__lead {
    max-width: 720px;
    margin: 0 auto 32px;
    text-align: center;
    color: #4b5563;
    line-height: 1.6;
    white-space: pre-line;
}

.quiz-block__layout {
    display: grid;
    grid-template-columns: 300px minmax(0, 1fr);
    gap: 24px;
    align-items: start;
}

/* Левая панель бонусов */
.quiz-info {
    padding: 24px;
    border-radius: 20px;
    background-color: #1e3a8a;
    background-image: linear-gradient(160deg, #1e3a8a, #2563eb);
    color: #fff;
    box-shadow: 0 16px 32px rgba(30, 58, 138, 0.24);
}

.quiz-info__title {
    margin: 0 0 20px;
    font-size: 1.15rem;
    font-weight: 700;
    line-height: 1.4;
    white-space: pre-line;
}

.quiz-info__items {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.quiz-info__item {
    display: flex;
    gap: 12px;
    align-items: center;
}

.quiz-info__item-icon {
    display: flex;
    flex: none;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.16);
    font-weight: 700;
}

.quiz-info__item-icon img {
    display: block;
    width: 24px;
    height: 24px;
    object-fit: contain;
}

.quiz-info__item-title {
    font-size: 0.95rem;
    line-height: 1.45;
}

/* Карточка с вопросами */
.quiz-card {
    padding: 28px;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 16px 40px rgba(17, 24, 39, 0.08);
}

.quiz-progress {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 22px;
}

.quiz-progress__bar {
    flex: 1 1 auto;
    height: 6px;
    border-radius: 999px;
    background: #e5e7eb;
    overflow: hidden;
}

.quiz-progress__bar span {
    display: block;
    height: 100%;
    border-radius: 999px;
    background: #2563eb;
    transition: width 0.25s ease;
}

.quiz-progress__count {
    flex: none;
    color: #6b7280;
    font-size: 0.85rem;
    white-space: nowrap;
}

.quiz-step {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 20px;
}

.quiz-step--media {
    grid-template-columns: 240px minmax(0, 1fr);
}

.quiz-step__media img {
    display: block;
    width: 100%;
    height: 100%;
    min-height: 220px;
    object-fit: cover;
    border-radius: 16px;
}

.quiz-step__head {
    display: flex;
    gap: 14px;
    align-items: flex-start;
}

.quiz-step__marker {
    display: flex;
    flex: none;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 1rem;
    font-weight: 700;
}

.quiz-step__marker--check {
    background: #dcfce7;
    color: #047857;
}

.quiz-step__title {
    margin: 3px 0 0;
    font-size: 1.25rem;
    line-height: 1.35;
    color: #111827;
}

.quiz-step__desc {
    margin: 8px 0 0;
    color: #4b5563;
    line-height: 1.55;
    white-space: pre-line;
}

.quiz-step__hint {
    margin: 6px 0 0;
    color: #6b7280;
    font-size: 0.9rem;
}

/* Варианты ответа */
.quiz-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 20px;
}

.quiz-option {
    position: relative;
    display: flex;
    gap: 12px;
    padding: 14px 16px;
    border: 1.5px solid #e5e7eb;
    border-radius: 14px;
    background: #fff;
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
}

.quiz-option:hover {
    border-color: #93c5fd;
    background: #f8fafc;
}

.quiz-option:focus-within {
    outline: 3px solid rgba(37, 99, 235, 0.35);
    outline-offset: 2px;
}

.quiz-option.is-selected {
    border-color: #2563eb;
    background: #eff6ff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.quiz-option input {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
}

.quiz-option__marker {
    position: relative;
    flex: none;
    width: 22px;
    height: 22px;
    margin-top: 1px;
    border: 2px solid #cbd5e1;
    border-radius: 50%;
    background: #fff;
    transition: border-color 0.15s ease, background 0.15s ease;
}

.quiz-option__marker::after {
    content: '';
    position: absolute;
    inset: 4px;
    border-radius: 50%;
    background: #fff;
    opacity: 0;
    transition: opacity 0.15s ease;
}

.quiz-option.is-selected .quiz-option__marker {
    border-color: #2563eb;
    background: #2563eb;
}

.quiz-option.is-selected .quiz-option__marker::after {
    opacity: 1;
}

/* Чекбокс для шагов с несколькими вариантами */
.quiz-option--checkbox .quiz-option__marker {
    border-radius: 6px;
}

.quiz-option--checkbox .quiz-option__marker::after {
    content: '✓';
    inset: 0;
    border-radius: 0;
    background: transparent;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    line-height: 18px;
    text-align: center;
}

.quiz-option__body {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.quiz-option__title {
    color: #111827;
    font-weight: 600;
    line-height: 1.4;
}

.quiz-option__desc {
    color: #6b7280;
    font-size: 0.88rem;
    line-height: 1.45;
}

/* Поля шага с контактами */
.quiz-fields {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 20px 0 14px;
}

.quiz-input {
    width: 100%;
    box-sizing: border-box;
    padding: 12px 14px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    background: #fff;
    color: #111827;
    font-size: 1rem;
    outline: none;
}

.quiz-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.quiz-consent {
    position: relative;
    display: flex;
    gap: 10px;
    align-items: flex-start;
    color: #4b5563;
    font-size: 0.85rem;
    line-height: 1.5;
    cursor: pointer;
}

.quiz-consent input {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
}

.quiz-consent__box {
    position: relative;
    flex: none;
    width: 18px;
    height: 18px;
    margin-top: 2px;
    border: 2px solid #cbd5e1;
    border-radius: 4px;
    background: #fff;
    transition: border-color 0.15s ease, background 0.15s ease;
}

.quiz-consent input:checked + .quiz-consent__box {
    border-color: #2563eb;
    background: #2563eb;
}

.quiz-consent input:checked + .quiz-consent__box::after {
    content: '✓';
    position: absolute;
    inset: 0;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    line-height: 14px;
    text-align: center;
}

.quiz-consent input:focus-visible + .quiz-consent__box {
    outline: 3px solid rgba(37, 99, 235, 0.35);
    outline-offset: 2px;
}

.quiz-consent__text a {
    color: #2563eb;
}

/* Кнопки и сообщения */
.quiz-buttons {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-top: 24px;
}

.quiz-btn {
    padding: 12px 22px;
    border: 1px solid transparent;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}

.quiz-btn--primary {
    background: #2563eb;
    color: #fff;
}

.quiz-btn--primary:hover:not(:disabled) {
    background: #1d4ed8;
}

.quiz-btn--ghost {
    background: #fff;
    border-color: #d1d5db;
    color: #374151;
}

.quiz-btn--ghost:hover:not(:disabled) {
    border-color: #2563eb;
    color: #2563eb;
}

.quiz-btn:disabled {
    opacity: 0.5;
    cursor: default;
}

.quiz-error {
    margin: 12px 0 0;
    color: #b91c1c;
    font-size: 0.9rem;
}

/* Экран «Спасибо» */
.quiz-success {
    padding: 16px 8px;
    text-align: center;
}

.quiz-success__marker {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: #dcfce7;
    color: #047857;
    font-size: 1.6rem;
    font-weight: 700;
}

.quiz-success__title {
    margin: 0 0 10px;
    font-size: 1.35rem;
    line-height: 1.35;
    color: #111827;
}

.quiz-success__desc {
    max-width: 520px;
    margin: 0 auto 20px;
    color: #4b5563;
    line-height: 1.6;
    white-space: pre-line;
}

/* Переход между шагами */
.quiz-slide-enter-active,
.quiz-slide-leave-active {
    transition: opacity 0.18s ease, transform 0.18s ease;
}

.quiz-slide-enter-from {
    opacity: 0;
    transform: translateX(24px);
}

.quiz-slide-leave-to {
    opacity: 0;
    transform: translateX(-24px);
}

/* Адаптив */
@media (max-width: 900px) {
    .quiz-block__layout {
        grid-template-columns: 1fr;
    }

    .quiz-info {
        order: 2;
    }

    .quiz-card {
        order: 1;
    }
}

@media (max-width: 640px) {
    .quiz-block {
        padding: 40px 14px 56px;
    }

    .quiz-block__title {
        font-size: 1.6rem;
    }

    .quiz-card {
        padding: 20px;
    }

    .quiz-step--media {
        grid-template-columns: 1fr;
    }

    .quiz-step__media img {
        max-height: 200px;
        min-height: 0;
    }

    .quiz-buttons {
        flex-direction: column-reverse;
    }

    .quiz-btn {
        width: 100%;
    }
}


</style>


