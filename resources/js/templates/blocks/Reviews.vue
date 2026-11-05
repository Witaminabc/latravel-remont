<template>
    <section v-if="reviews.length" class="reviews">
        <div class="reviews__inner">
            <h2 class="reviews__heading">{{ heading }}</h2>

            <div class="reviews__grid">
                <article
                    v-for="(item, index) in reviews"
                    :key="index"
                    class="review"
                >
                    <div class="review__header">
                        <img
                            v-if="item.photo"
                            :src="item.photo"
                            :alt="`Фото ${item.author_name}`"
                            class="review__avatar"
                        />
                        <div v-else class="review__avatar review__avatar--placeholder">
                            {{ avatarInitials(item.author_name) }}
                        </div>
                        <div class="review__author">
                            <div class="review__author-name">{{ item.author_name }}</div>
                            <div v-if="item.author_position" class="review__author-position">
                                {{ item.author_position }}
                            </div>
                        </div>
                        <div class="review__rating">
                            <span v-for="star in 5" :key="star" class="review__star"
                                  :class="{ 'is-active': star <= item.rating }">★</span>
                        </div>
                    </div>

                    <div class="review__body">
                        <p class="review__text">{{ item.text }}</p>
                    </div>

                    <div class="review__footer">
                        <time v-if="item.date" class="review__date">{{ item.date }}</time>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>

<script setup>
/**
 * Блок «Отзывы клиентов» (группа group_home, поле reviews).
 * Дизайн по аналогии с granat.studio: карточки с аватаром, именем,
 * звёздным рейтингом, текстом и датой.
 *
 * Данные — ACF-репитер group_home.reviews. Пусто → демо-отзывы FALLBACK_REVIEWS.
 */
import { computed } from 'vue'

const props = defineProps({
    heading: { type: String, default: 'Отзывы наших клиентов' },
    reviews: { type: Array, default: () => [] },
})

const FALLBACK_REVIEWS = [
    {
        author_name: 'Екатерина С.',
        author_position: 'владелец квартиры, Казань',
        rating: 5,
        text: 'Ремонт сделали под ключ за 45 дней. Собрали мебель, уложили плитку, подключили технику. Остались очень довольны результатом.',
        photo: '/storage/uploads/face1.jpg',
        date: '14 марта 2024',
    },
    {
        author_name: 'Андрей М.',
        author_position: 'заказчик, ЖК «Сиберово»',
        rating: 5,
        text: 'Делали двухкомнатную квартиру в новостройке. Смету не превысили, все работы выполнили строго по договору. Рекомендую.',
        photo: '/storage/uploads/portfolio/gotovyj-remont-2k-kvartiry-zhk-siberovo-kazan-17-800x600.jpg',
        date: '10 февраля 2024',
    },
    {
        author_name: 'Мария К.',
        author_position: 'заказчик, г. Казань',
        rating: 4,
        text: 'Отремонтировали санузел под ключ. Мастера пришли вовремя, убрали за собой. Качество работы на высоте.',
        photo: '/storage/uploads/portfolio/Ванная-1-800x600.jpg',
        date: '25 января 2024',
    },
    {
        author_name: 'Сергей В.',
        author_position: 'заказчик, ЖК «Меркурий»',
        rating: 5,
        text: 'Полный ремонт квартиры с дизайн-проектом. Сделали быстро, без задержек. Спасибо команде за профессионализм.',
        photo: '/storage/uploads/portfolio/gotovyj-remont-dvuhkomnatnoj-kvartiry-zhk-merkurij-1-800x600.jpg',
        date: '18 декабря 2023',
    },
]

const displayReviews = computed(() => {
    const items = props.reviews
    if (!Array.isArray(items) || items.length === 0) return FALLBACK_REVIEWS
    return items.filter(item => item && item.author_name && item.text)
})

const reviews = computed(() => displayReviews.value)

function avatarInitials(name) {
    if (!name) return '?'
    const parts = name.trim().split(/\s+/)
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase()
    }
    return name[0].toUpperCase()
}
</script>

<style scoped>
.reviews {
    padding: 56px 20px 80px;
    background: #fff;
}

.reviews__inner {
    max-width: 1120px;
    margin: 0 auto;
}

.reviews__heading {
    text-align: center;
    font-size: 2rem;
    margin: 0 0 40px;
    color: #111827;
}

.reviews__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

.review {
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    padding: 24px;
    background: #fff;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.review:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(17, 24, 39, 0.08);
    border-color: #d1d5db;
}

.review__header {
    display: flex;
    gap: 16px;
    align-items: flex-start;
    margin-bottom: 16px;
}

.review__avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.review__avatar--placeholder {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.review__author {
    flex: 1;
}

.review__author-name {
    margin: 0 0 4px;
    font-size: 1.05rem;
    font-weight: 600;
    color: #111827;
}

.review__author-position {
    margin: 0;
    font-size: 0.85rem;
    color: #6b7280;
    line-height: 1.4;
}

.review__rating {
    display: flex;
    gap: 4px;
    margin-left: 12px;
    flex-shrink: 0;
}

.review__star {
    color: #d1d5db;
    font-size: 1.25rem;
    line-height: 1;
}

.review__star.is-active {
    color: #f59e0b;
}

.review__body {
    flex: 1;
}

.review__text {
    margin: 0 0 16px;
    color: #374151;
    font-size: 0.95rem;
    line-height: 1.6;
}

.review__footer {
    margin-top: auto;
}

.review__date {
    display: block;
    margin: 0;
    font-size: 0.85rem;
    color: #9ca3af;
}

@media (max-width: 640px) {
    .reviews {
        padding: 40px 16px 56px;
    }

    .reviews__heading {
        font-size: 1.6rem;
        margin-bottom: 28px;
    }

    .reviews__grid {
        grid-template-columns: 1fr;
    }

    .review__header {
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 12px;
    }

    .review__rating {
        margin-left: 0;
        margin-top: 8px;
        justify-content: center;
    }

    .review__author-position {
        text-align: center;
    }
}
</style>