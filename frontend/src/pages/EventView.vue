<script setup lang="ts">
import { onMounted, computed, ref, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import EventForm from '@/components/EventForm.vue'
import type { EventModel } from '@/stores/event'
import { useEventStore } from '@/stores/event'
import { useAuthStore } from '@/stores/auth'
import EventDetails from '@/components/EventDetails.vue'

const router = useRouter()
const route = useRoute()
const eventStore = useEventStore()
const authStore = useAuthStore()

const eventForm = ref<InstanceType<typeof EventForm> | null>(null)

const isEditMode = computed(() => route.query.edit === '1')
const isCreateMode = computed(() => route.path.endsWith('/create'))
const eventId = computed(() => Number(route.params.id))

const event = ref<EventModel | null>(null)
const isLoading = ref(!isCreateMode.value)
const errorMessage = ref('')
const isSaving = ref(false)
const isDeleting = ref(false)
const isJoining = ref(false)

const isOwner = computed(() => {
  if (isCreateMode.value) return true
  if (!event.value || !authStore.user) return false
  return Number(event.value.owner?.id) === Number(authStore.user.id)
})

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

// Dynamic text for non-owner action button based on attendance
const attendButtonText = computed(() => {
  if (isJoining.value) return 'Updating...'
  return event.value?.is_attending ? 'Leave Event' : 'Join Event'
})

onMounted(async () => {
  if (isCreateMode.value) {
    isLoading.value = false
    return
  }

  if (!eventId.value) {
    errorMessage.value = 'Missing event ID.'
    isLoading.value = false
    return
  }

  const cachedEvent = sessionStorage.getItem(`event:${eventId.value}`)
  if (cachedEvent) {
    try {
      event.value = JSON.parse(cachedEvent)
    } catch (e) {
      console.warn('Failed to parse cached event data', e)
    }
  }

  try {
    const fetchedEvent = await eventStore.fetchEvent(eventId.value)
    event.value = fetchedEvent
  } catch (error) {
    console.error('Failed to load event:', error)
    if (!event.value) {
      errorMessage.value = 'Failed to load event.'
    }
  } finally {
    isLoading.value = false
    if (isEditMode.value && event.value) {
      await nextTick()
      eventForm.value?.load(event.value)
    }
  }
})

watch(isEditMode, async (editing) => {
  if (!editing || !event.value) return
  await nextTick()
  eventForm.value?.load(event.value)
})

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
      const updatedEvent = await eventStore.updateEvent(eventId.value, formData)
      event.value = updatedEvent
      router.push({ path: `/events/${eventId.value}`, query: {} })
    } else {
      const createdEvent = await eventStore.createEvent(formData)
      event.value = createdEvent
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

async function handleDelete() {
  if (!eventId.value) return

  const confirmed = window.confirm('Are you sure you want to delete this event?')
  if (!confirmed) return

  isDeleting.value = true
  errorMessage.value = ''

  try {
    await eventStore.deleteEvent(eventId.value)
    router.back()  
  } catch (error) {
    console.error('Failed to delete event:', error)
    errorMessage.value = 'Failed to delete event.'
  } finally {
    isDeleting.value = false
  }
}

async function handleJoin() {
  if (!eventId.value || isJoining.value) return

  isJoining.value = true
  errorMessage.value = ''

  try {
    await eventStore.joinEvent(eventId.value)
    event.value = await eventStore.fetchEvent(eventId.value)
  } catch (error) {
    console.error('Failed to update event attendance:', error)
    errorMessage.value = 'Failed to update attendance status.'
  } finally {
    isJoining.value = false
  }
}
</script>

<template>
  <section class="event-page">
    <header class="event-header">
      <div>
        <p class="eyebrow">{{ eyebrowText }}</p>
        <h1>{{ titleText }}</h1>
        <p class="subtitle">{{ subtitleText }}</p>
      </div>

      <div class="header-actions" v-if="!isLoading">
        <!-- OWNER ACTIONS -->
        <template v-if="isOwner">
          <button
            v-if="!isCreateMode"
            type="button"
            class="action-btn danger"
            :disabled="isSaving || isDeleting"
            @click="handleDelete"
          >
            {{ isDeleting ? 'Deleting...' : 'Delete' }}
          </button>

          <button
            v-if="isCreateMode || isEditMode"
            type="button"
            class="action-btn secondary"
            :disabled="isSaving || isDeleting"
            @click="handleCancel"
          >
            Cancel
          </button>

          <button
            type="button"
            class="action-btn primary"
            :disabled="isSaving || isDeleting"
            @click="handleSubmit"
          >
            {{ buttonText }}
          </button>
        </template>

        <!-- NON-OWNER ACTIONS -->
        <template v-else>
          <button
            type="button"
            class="action-btn"
            :class="event?.is_attending ? 'secondary-danger' : 'primary'"
            :disabled="isJoining"
            @click="handleJoin"
          >
            {{ attendButtonText }}
          </button>
        </template>
      </div>
    </header>

    <div v-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </div>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="event-content">
      <div v-if="isLoading" class="loading-state">Loading event...</div>

      <template v-else>
        <EventDetails v-if="!isCreateMode && !isEditMode && event" :event="event" />
        <EventForm v-else-if="isCreateMode || isEditMode" ref="eventForm" :editable="true" />
      </template>
    </div>
  </section>
</template>

<style scoped>
.event-page {
  width: min(100%, 1100px);
  margin: 0 auto;
  padding: 1.5rem 1.5rem 3rem;
  box-sizing: border-box;
  color: var(--color-text);
}

.event-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.eyebrow {
  margin: 0 0 0.25rem;
  color: var(--color-primary, #14b8a6);
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.event-header h1 {
  margin: 0;
  font-size: 1.75rem;
  font-weight: 700;
}

.subtitle {
  margin: 0.35rem 0 0;
  color: var(--color-text-muted, #9ca3af);
  font-size: 0.95rem;
}

.header-actions {
  display: flex;
  gap: 0.6rem;
  flex-shrink: 0;
}

.action-btn {
  border: none;
  border-radius: 0.6rem;
  padding: 0.6rem 1.1rem;
  color: var(--color-text);
  cursor: pointer;
  font: inherit;
  font-weight: 600;
  transition: opacity 0.15s ease, background-color 0.15s ease;
}

.action-btn.primary {
  background: var(--color-primary, #14b8a6);
  color: #fff;
}

.action-btn.secondary {
  background: rgba(255, 255, 255, 0.08);
}

.action-btn.secondary-danger {
  background: rgba(244, 63, 94, 0.15);
  border: 1px solid rgba(244, 63, 94, 0.3);
  color: #fda4af;
}

.action-btn.danger {
  background: rgba(244, 63, 94, 0.15);
  color: #fda4af;
}

.action-btn:hover:not(:disabled) {
  opacity: 0.88;
}

.action-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.loading-state {
  min-height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-muted);
}

.error-message {
  margin-bottom: 1.25rem;
  padding: 0.75rem 1rem;
  border-radius: 0.6rem;
  background: rgba(244, 63, 94, 0.1);
  color: #fda4af;
}

@media (max-width: 700px) {
  .header-actions {
    width: 100%;
  }

  .action-btn {
    flex: 1;
  }
}
</style>