<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

const { t } = useI18n()
const router = useRouter()

interface EventCardProps {
  id?: string
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

const eventId = computed(() => {
  if (props.id && props.id.trim().length > 0) {
    return props.id
  }

  return `${props.name}-${props.date}-${props.location}`
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '')
})

function openEvent(editMode = false) {
  const payload = {
    id: eventId.value,
    name: props.name,
    date: props.date,
    location: props.location,
    image: props.image,
    type: props.type,
    description: props.description,
  }

  sessionStorage.setItem(`event:${eventId.value}`, JSON.stringify(payload))
  router.push({
    name: 'event-view',
    params: { id: eventId.value },
    query: editMode ? { edit: '1' } : {},
  })
}
</script>

<template>
  <article
    class="event-card"
    role="button"
    tabindex="0"
    :aria-label="`Open ${props.name} details`"
    @click="openEvent(false)"
    @keydown.enter.prevent="openEvent(false)"
    @keydown.space.prevent="openEvent(false)"
  >
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

      <div class="event-actions">
        <button
          type="button"
          class="event-action-btn ghost"
          @click.stop="openEvent(true)"
          @keydown.stop
        >
          Edit
        </button>
      </div>
    </div>
  </article>
</template>

<style scoped>
.event-card {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 0.9rem;
  padding: 0.5rem;
  color: var(--color-text);
  box-sizing: border-box;
  cursor: pointer;
}

.event-card:focus-visible {
  outline: 2px solid rgba(20, 184, 166, 0.75);
  outline-offset: 2px;
}

.event-image-wrap {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 9;
  border-radius: 0.7rem;
  overflow: hidden;
  background: #f3f4f6;
}

.event-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.event-content {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  padding: 0 0.1rem;
}

.event-header {
  display: flex;
  justify-content: flex-start;
}

.event-type {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  background: rgba(20, 184, 166, 0.12);
  color: var(--color-primary);
  font-size: 0.62rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.event-title {
  margin: 0;
  font-size: 1rem;
  line-height: 1.3;
  color: var(--color-text);
}

.event-meta {
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.meta-item dt {
  font-size: 0.62rem;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--color-text-muted);
  font-weight: 700;
}

.meta-item dd {
  margin: 0;
  color: var(--color-text);
  font-weight: 500;
  font-size: 0.8rem;
}

.event-description {
  display: none;
}

.event-actions {
  margin-top: 0.25rem;
  display: flex;
  gap: 0.45rem;
}

.event-action-btn {
  border: 1px solid rgba(255, 255, 255, 0.16);
  background: rgba(20, 184, 166, 0.12);
  color: var(--color-text);
  border-radius: 0.55rem;
  padding: 0.35rem 0.6rem;
  font-size: 0.74rem;
  cursor: pointer;
}

.event-action-btn.ghost {
  background: rgba(255, 255, 255, 0.06);
}
</style>