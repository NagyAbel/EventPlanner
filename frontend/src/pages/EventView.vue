<script setup lang="ts">
import { onMounted ,computed, ref,nextTick,watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import EventForm from '@/components/EventForm.vue'
import type { EventModel } from '@/stores/event'
import { useEventStore } from '@/stores/event'
import EventDetails from '@/components/EventDetails.vue'
const router = useRouter()
const route = useRoute()
const eventStore = useEventStore()

const eventForm = ref<InstanceType<typeof EventForm> | null>(null)

const isEditMode = computed(() => route.query.edit === '1')
const isCreateMode = computed(() => route.path.endsWith('/create'))
const eventId = computed(() => Number(route.params.id))
const eyebrowText = computed(() => {
  if (isCreateMode.value) return 'New Event'
  if (isEditMode.value) return 'Updating Event'
  return 'Event Overview'
})

const titleText = computed(() => {
  if (isCreateMode.value) return 'Create Event'
  if (isEditMode.value) return 'Edit Event'
  return 'Event Details'
})

const subtitleText = computed(() => {
  if (isCreateMode.value) return 'Create an event and invite people to join.'
  if (isEditMode.value) return 'Update details for this event.'
  return 'View event information and guest list.'
})

const buttonText = computed(() => {
  if (isSaving.value) return isEditMode.value ? 'Saving...' : 'Creating...'
  if (isCreateMode.value) return 'Create Event'
  if (isEditMode.value) return 'Save Changes'
  return 'Edit'
})

const event = ref<EventModel | null>(null)
onMounted(async () => {
  if (isCreateMode.value) {
    return
  }

  if (!eventId.value) {
    errorMessage.value = 'Missing event ID.'
    return
  }

  try {
    const fetchedEvent = await eventStore.fetchEvent(eventId.value);
    event.value = fetchedEvent

    if (isEditMode.value) {
      await nextTick()
      eventForm.value?.load(fetchedEvent)
    }
  } catch (error) {
    console.error('Failed to load event:', error)
    errorMessage.value = 'Failed to load event.'
  }
})

watch(isEditMode, async (editing) => {
  if (!editing || !event.value) {
    return
  }

  await nextTick()

  eventForm.value?.load(event.value)
})
const errorMessage = ref('')
const isSaving = ref(false)

async function handleSubmit() {
  if (!isCreateMode.value && !isEditMode.value) {
    router.push({ query: { ...route.query, edit: '1' } })
    return
  }

  errorMessage.value = ''
  const formData = eventForm.value?.get()

  if (!formData) {
    errorMessage.value = 'Unable to read event form.'
    return
  }

  isSaving.value = true

  try {
    if (isEditMode.value) {
      // Call update API and clear query parameters back to view mode
       const updatedEvent = await eventStore.updateEvent(eventId.value,formData)
        event.value = updatedEvent

      router.push({ path: `/events/${eventId.value}`, query: {} })
    } else {
      // Call create API
      const createdEvent = await eventStore.createEvent(formData)
      router.push(`/events/${createdEvent.id}`)
    }
  } catch (error) {
    console.error('Failed to save event:', error)
    errorMessage.value = isEditMode.value
      ? 'Failed to update event.'
      : 'Failed to create event.'
  } finally {
    isSaving.value = false
  }
}

function handleCancel() {
  if (isEditMode.value) {
    router.push({ path: route.path, query: {} })
  } else {
    router.back()
  }
}
</script>

<template>
  <section class="create-event-page">
    <header class="create-event-header">
  <div>
    <p class="eyebrow">{{ eyebrowText }}</p>
    <h1>{{ titleText }}</h1>
    <p class="subtitle">{{ subtitleText }}</p>
  </div>

<div class="header-actions">
  <button
    v-if="isCreateMode || isEditMode"
    type="button"
    class="action-btn secondary"
    :disabled="isSaving"
    @click="handleCancel"
  >
    Cancel
  </button>

  <button
    type="button"
    class="action-btn primary"
    :disabled="isSaving"
    @click="handleSubmit"
  >
    {{ buttonText }}
  </button>
</div></header>
    <div
      v-if="errorMessage"
      class="error-message"
    >
      {{ errorMessage }}
    </div>

    <article class="create-event-card">
      <EventDetails v-if="!isCreateMode && !isEditMode && event" :event="event"/>
      <EventForm v-else-if="isCreateMode || isEditMode" ref="eventForm" :editable="true"/>

      <div v-else class="loading-state">Loading event...</div>
    </article>
  </section>
</template>

<style scoped>
.create-event-page {
  width: min(100%, 1100px);
  margin: 0 auto;
  padding: 1.25rem 1.25rem 2rem;
  box-sizing: border-box;
  color: var(--color-text);
}

.create-event-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1.5rem;
  margin-bottom: 1rem;
}

.eyebrow {
  margin: 0 0 0.25rem;
  color: var(--color-primary);
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.create-event-header h1 {
  margin: 0;
  font-size: 1.55rem;
}

.subtitle {
  margin: 0.35rem 0 0;
  color: var(--color-text-muted);
  font-size: 0.9rem;
}

.header-actions {
  display: flex;
  gap: 0.6rem;
  flex-shrink: 0;
}

.action-btn {
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 0.7rem;
  padding: 0.55rem 0.95rem;
  color: var(--color-text);
  cursor: pointer;
  font: inherit;
  font-weight: 600;
}
.loading-state {
  min-height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-muted);
}
.action-btn.primary {
  background: rgba(20, 184, 166, 0.18);
}

.action-btn.secondary {
  background: rgba(255, 255, 255, 0.06);
}

.action-btn:hover:not(:disabled) {
  border-color: rgba(20, 184, 166, 0.5);
}

.action-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.create-event-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
  padding: 0.75rem;
}

.error-message {
  margin-bottom: 0.9rem;
  padding: 0.75rem 0.9rem;
  border: 1px solid rgba(244, 63, 94, 0.25);
  border-radius: 0.7rem;
  background: rgba(244, 63, 94, 0.08);
  color: #fda4af;
}

@media (max-width: 700px) {
  .create-event-header {
    align-items: stretch;
    flex-direction: column;
  }

  .header-actions {
    width: 100%;
  }

  .action-btn {
    flex: 1;
  }
}
</style>