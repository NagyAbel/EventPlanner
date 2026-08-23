<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

const { t } = useI18n()
const router = useRouter()

interface EventCardProps {
  id: string | number
  name: string
  date: string
  location: string
  cover_image: string
  type: string
  description: string | null,
}

const props = defineProps<EventCardProps>()

const labels = {
  date: t('event.date'),
  location: t('event.location'),
}

const eventId = computed(() => String(props.id))

function openEvent(editMode = false) {
  const id = eventId.value

  if (!id || id === 'undefined' || id === 'null') {
    console.error('Cannot open event: missing event ID', props)
    return
  }

  const payload = {
    id,
    name: props.name,
    date: props.date,
    location: props.location,
    cover_image: props.cover_image,
    type: props.type,
  }

  sessionStorage.setItem(
    `event:${id}`,
    JSON.stringify(payload),
  )

  router.push({
    name: 'event-view',
    params: {
      id,
    },
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
      <img
        :src="props.cover_image"
        :alt="props.name"
        class="event-image"
      />
    </div>

    <div class="event-content">
      <div class="event-header">
        <span class="event-type">
          {{ props.type }}
        </span>
      </div>

      <h2 class="event-title">
        {{ props.name }}
      </h2>

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
    </div>
  </article>
</template>

<style scoped>
.event-card {
  width: 100%;
  height: 100%;
  min-height: 0;
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
  overflow: hidden;
}

.event-card:focus-visible {
  outline: 2px solid rgba(20, 184, 166, 0.75);
  outline-offset: 2px;
}

.event-image-wrap {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 9;
  flex-shrink: 0;
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
  min-height: 0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  padding: 0 0.1rem;
}

.event-header {
  display: flex;
  justify-content: flex-start;
  flex-shrink: 0;
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

  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.event-meta {
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  flex-shrink: 0;
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
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.event-description {
  display: none;
}

.event-actions {
  margin-top: auto;
  display: flex;
  gap: 0.45rem;
  flex-shrink: 0;
}
</style>