<script setup lang="ts">
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

interface EventCardProps {
  name: string
  date: string
  location: string
  image: string
  type: string
  description: string
}

const props = withDefaults(defineProps<EventCardProps>(), {
  name: 'Event Title',
  date: '2026.01.15',
  location: 'Budapest',
  image:
    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=80',
  type: 'Koncert',
  description:
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero.',
})

const labels = {
  date: t('event.date', 'Dátum'),
  location: t('event.location', 'Helyszín'),
  type: t('event.type', 'Típus'),
  description: t('event.description', 'Leírás'),
}
</script>

<template>
  <article class="event-card">
    <div class="event-image-wrap">
      <img :src="props.image" :alt="props.name" class="event-image" />
    </div>

    <div class="event-content">
      <div class="event-header">
        <span class="event-type">{{ props.type }}</span>
      </div>

      <h2 class="event-title">{{ props.name }}</h2>

      <dl class="event-meta">
        <div class="meta-item">
          <dt>{{ labels.date }}</dt>
          <dd>{{ props.date }}</dd>
        </div>

        <div class="meta-item">
          <dt>{{ labels.location }}</dt>
          <dd>{{ props.location }}</dd>
        </div>
      </dl>

      <div class="event-description">
        <h3>{{ t('event.description', 'Leírás') }}</h3>
        <p>{{ props.description }}</p>
      </div>
    </div>
  </article>
</template>

<style scoped>
.event-card {
  margin:50px auto;
  display: grid;
  grid-template-columns: minmax(220px, 300px) minmax(0, 1fr);
  background: #ffffff;
  border: 1px solid #e5e7eb;
  overflow: hidden;
  box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
  width: 95%;
}

.event-image-wrap {
  position: relative;
  min-height: 220px;
  background: #f3f4f6;
}

.event-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.event-content {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.event-header {
  display: flex;
  justify-content: flex-start;
}

.event-type {
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 0.8rem;
  border-radius: 999px;
  background: #eef2ff;
  color: #4338ca;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.event-title {
  margin: 0;
  font-size: clamp(1.8rem, 3vw, 2.4rem);
  line-height: 1.1;
  color: #111827;
}

.event-meta {
  margin: 0;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.9rem;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  padding: 0.8rem 0.9rem;
  border-radius: 12px;
  background: #f9fafb;
  border: 1px solid #edf2f7;
}

.meta-item dt {
  font-size: 0.72rem;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #6b7280;
  font-weight: 700;
}

.meta-item dd {
  margin: 0;
  color: #111827;
  font-weight: 600;
}

.event-description {
  padding-top: 0.5rem;
  border-top: 1px solid #e5e7eb;
}

.event-description h3 {
  margin: 0 0 0.5rem;
  font-size: 0.85rem;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #6b7280;
}

.event-description p {
  margin: 0;
  color: #374151;
  line-height: 1.6;
}

@media (max-width: 1000px) {
  .event-card {
    grid-template-columns: 1fr;
    box-sizing: border-box;
  }
  .event-content {
    padding: 1.25rem;
  }

  .event-meta {
    grid-template-columns: 1fr;
  }
}

@media(max-width:500px){
  .event-card{
    width: 100%;
  }

}
</style>