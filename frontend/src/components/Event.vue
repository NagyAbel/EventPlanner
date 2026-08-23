<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import type {
  EventTypeModel,
} from '@/stores/event'

const { t } = useI18n()
const router = useRouter()

interface EventCardProps {
  id: string | number
  name: string
  date: string
  location: string
  cover_image?: string | null
  type: EventTypeModel
  description?: string | null
}

const props = defineProps<EventCardProps>()

const labels = {
  date: t('event.date'),
  location: t('event.location'),
  noDate: t('event.no_date'),
  description: t('event.description'),
}

const eventId = computed(() => String(props.id))

/**
 * Normalizes date strings (supports YYYY-MM-DDTHH:mm and standard ISO timestamps)
 * into a valid JS Date object.
 */
const parseDate = (dateStr: string): Date | null => {
  if (!dateStr) return null
  const normalized = dateStr.includes(' ') ? dateStr.replace(' ', 'T') : dateStr
  const dateObj = new Date(normalized)
  return isNaN(dateObj.getTime()) ? null : dateObj
}

const formattedDate = computed(() => {
  const dateObj = parseDate(props.date)
  if (!dateObj) return labels.noDate

  return new Intl.DateTimeFormat('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
  }).format(dateObj)
})

const formattedTime = computed(() => {
  const dateObj = parseDate(props.date)
  if (!dateObj) return ''

  return new Intl.DateTimeFormat('en-US', {
    hour: 'numeric',
    minute: '2-digit',
  }).format(dateObj)
})

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
    params: { id },
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
    <!-- Image Wrapper with Title Overlay -->
    <div class="event-image-wrap">
      <img
        v-if="props.cover_image"
        :src="props.cover_image"
        :alt="props.name"
        class="event-image"
        loading="lazy"
      />
      <div v-else class="event-image-placeholder" />

      <!-- Floating Type Badge -->
      <span class="event-type-badge">
        {{ props.type.name }}
      </span>

      <!-- Title Overlay (Bottom-Left) -->
      <div class="image-overlay">
        <h3 class="event-title" :title="props.name">
          {{ props.name }}
        </h3>
      </div>
    </div>

    <!-- Content Body -->
    <div class="event-content">
      <!-- Date & Location Row -->
      <dl class="event-meta-row">
        <!-- Date Block -->
        <div class="meta-item">
          <dt>{{ labels.date }}</dt>
          <dd>
            <span class="date-text">{{ formattedDate }}</span>
            <span v-if="formattedTime" class="time-text">• {{ formattedTime }}</span>
          </dd>
        </div>

        <!-- Vertical Separator Line -->
        <div class="meta-divider" role="separator" aria-orientation="vertical" />

        <!-- Location Block -->
        <div class="meta-item meta-item-location">
          <dt>{{ labels.location }}</dt>
          <dd :title="props.location">
            {{ props.location || '—' }}
          </dd>
        </div>
      </dl>

      <!-- Horizontal Divider -->
      <hr v-if="props.description" class="section-divider" />

      <!-- Description Block -->
      <div v-if="props.description" class="description-block">
        <span class="description-label">{{ labels.description }}</span>
        <p class="event-description">
          {{ props.description }}
        </p>
      </div>
    </div>
  </article>
</template>

<style scoped>
.event-card {
  width: 100%;
  height: 100%;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
  padding: 0.75rem;
  color: var(--color-text);
  cursor: pointer;
  overflow: hidden;
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1),
              border-color 0.25s ease,
              box-shadow 0.25s ease,
              background-color 0.25s ease;
}

.event-card:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(20, 184, 166, 0.4);
  box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.5);
}

.event-card:focus-visible {
  outline: 2px solid rgba(20, 184, 166, 0.8);
  outline-offset: 2px;
}

/* Image Wrapper */
.event-image-wrap {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 9;
  flex-shrink: 0;
  border-radius: 0.75rem;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.04);
}

.event-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.event-card:hover .event-image {
  transform: scale(1.04);
}

.event-image-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(20, 184, 166, 0.1) 100%);
}

/* Type Badge */
.event-type-badge {
  position: absolute;
  top: 0.65rem;
  left: 0.65rem;
  z-index: 2;
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.65rem;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(20, 184, 166, 0.3);
  color: #14b8a6;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

/* Gradient Overlay for Title */
.image-overlay {
  position: absolute;
  inset: 0;
  z-index: 1;
  display: flex;
  align-items: flex-end;
  padding: 0.85rem;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.2) 60%, transparent 100%);
}

.event-title {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.25;
  color: #ffffff;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
  
  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

/* Content Body */
.event-content {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  padding: 0.75rem 0.25rem 0.25rem;
}

/* Meta Grid with Vertical Divider */
.event-meta-row {
  margin: 0;
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
  align-items: center;
  gap: 0.75rem;
}

.meta-divider {
  width: 1px;
  height: 80%;
  background: rgba(255, 255, 255, 0.12);
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  min-width: 0;
}

.meta-item dt {
  font-size: 0.63rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #14b8a6;
  font-weight: 700;
}

.meta-item dd {
  margin: 0;
  color: var(--color-text);
  font-weight: 600;
  font-size: 0.82rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.time-text {
  color: var(--color-text-muted);
  font-size: 0.76rem;
  font-weight: 400;
}

/* Horizontal Section Divider */
.section-divider {
  border: none;
  height: 1px;
  background: linear-gradient(
    90deg, 
    transparent, 
    rgba(255, 255, 255, 0.1) 20%, 
    rgba(255, 255, 255, 0.1) 80%, 
    transparent
  );
  margin: 0.75rem 0;
}

/* Description Section */
.description-block {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.description-label {
  font-size: 0.63rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--color-text-muted);
  font-weight: 700;
  opacity: 0.8;
}

.event-description {
  margin: 0;
  color: rgba(255, 255, 255, 0.75);
  font-size: 0.82rem;
  line-height: 1.45;
  
  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
}
</style>