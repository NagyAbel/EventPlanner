<script setup lang="ts">
import { onMounted, computed, ref, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import EventForm from '@/components/EventForm.vue'
import type { EventModel } from '@/stores/event'
import { useEventStore } from '@/stores/event'
import { useAuthStore } from '@/stores/auth'
import EventDetails from '@/components/EventDetails.vue'
import { useI18n } from 'vue-i18n'

const router = useRouter()
const route = useRoute()
const eventStore = useEventStore()
const authStore = useAuthStore()
const { t } = useI18n()

const eventForm = ref<InstanceType<typeof EventForm> | null>(null)

const isEditMode = computed(() => route.query.edit === '1' && isOwner)
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

const titleText = computed(() => {
  if (isCreateMode.value) return t('eventView.createEvent')
  if (isEditMode.value) return t('eventView.editEvent')
  return t('eventView.eventOverview')
})

const buttonText = computed(() => {
  if (isSaving.value) return isEditMode.value ? t('eventView.saving') : t('eventView.creating')
  if (isCreateMode.value) return t('eventView.createEvent')
  if (isEditMode.value) return t('eventView.saveChanges')
  return t('eventView.edit')
})

const attendButtonText = computed(() => {
  if (isJoining.value) return t('eventView.updating')
  return event.value?.is_attending ? t('eventView.leaveEvent') : t('eventView.joinEvent')
})

function handleGoBack() {
  if (window.history.length > 1) {
    router.back()
  } else {
    router.push('/events')
  }
}

onMounted(async () => {
  if (isCreateMode.value) {
    isLoading.value = false
    return
  }

  if (!eventId.value) {
    errorMessage.value = t('eventView.missingEventId')
    isLoading.value = false
    return
  }

  try {
    event.value = await eventStore.fetchEvent(eventId.value)
    errorMessage.value = ''

    if (isEditMode.value && !isOwner.value) {
      router.replace({ path: route.path, query: {} })
    }
  } catch (error) {
    console.error('Failed to load event:', error)
    event.value = null
    errorMessage.value = t('eventView.failedToLoadEvent')
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
    router.replace({ query: { ...route.query, edit: '1' } })
    return
  }
  const isValid = eventForm.value?.validate()
  if (!isValid) return

  
  errorMessage.value = ''
  const formData = eventForm.value?.get()

  if (!formData) {
    errorMessage.value = t('eventView.unableToReadForm')
    return
  }

  isSaving.value = true

  try {
    if (isEditMode.value) {
      const updatedEvent = await eventStore.updateEvent(eventId.value, formData)
      event.value = updatedEvent
      router.replace({ path: `/events/${eventId.value}`, query: {} })
    } else {
      const createdEvent = await eventStore.createEvent(formData)
      event.value = createdEvent
      router.replace(`/events/${createdEvent.id}`)
    }
  } catch (error) {
    console.error('Failed to save event:', error)
    errorMessage.value = isEditMode.value
      ? t('eventView.failedToUpdateEvent')
      : t('eventView.failedToCreateEvent')
  } finally {
    isSaving.value = false
  }
}

function handleCancel() {
  if (isEditMode.value) {
    router.replace({ path: route.path, query: {} })
  } else {
    handleGoBack()
  }
}

async function handleDelete() {
  if (!eventId.value) return

  const confirmed = window.confirm(t('eventView.deleteConfirmation'))
  if (!confirmed) return

  isDeleting.value = true
  errorMessage.value = ''

  try {
    await eventStore.deleteEvent(eventId.value)
    handleGoBack()
  } catch (error) {
    console.error('Failed to delete event:', error)
    errorMessage.value = t('eventView.failedToDeleteEvent')
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
    errorMessage.value = t('eventView.failedToUpdateAttendance')
  } finally {
    isJoining.value = false
  }
}
</script>

<template>
  <section class="event-page">
    <header class="event-header">
      <div class="header-left">
        <button type="button" class="back-btn" @click="handleGoBack">
          <svg
            class="back-icon"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
          <span>{{ t('eventView.back') }}</span>
        </button>
      </div>

      <div class="header-title">
        <h1>{{ titleText }}</h1>
      </div>

      <div class="header-actions">
        <template v-if="!isLoading && (event || isCreateMode)">
          <!-- OWNER ACTIONS -->
          <template v-if="isOwner">
            <button
              v-if="!isCreateMode"
              type="button"
              class="action-btn danger"
              :disabled="isSaving || isDeleting"
              @click="handleDelete"
            >
              {{ isDeleting ? t('eventView.deleting') : t('eventView.delete') }}
            </button>

            <button
              v-if="isCreateMode || isEditMode"
              type="button"
              class="action-btn secondary"
              :disabled="isSaving || isDeleting"
              @click="handleCancel"
            >
              {{ t('eventView.cancel') }}
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
        </template>
      </div>
    </header>

    <!-- LOADING STATE -->
    <div v-if="isLoading" class="loading-state">{{ t('eventView.loadingEvent') }}</div>

    <!-- FULL ERROR CARD (Shown when fetch fails or event missing) -->
    <div v-else-if="errorMessage && !event && !isCreateMode" class="error-card">
      <div class="error-icon-wrapper">
        <svg
          class="error-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
      </div>
      <h3>{{ t('eventView.unableToLoadEvent') }}</h3>
      <p>{{ errorMessage }}</p>
      <button type="button" class="action-btn secondary" @click="handleGoBack">
        {{ t('eventView.returnToEvents') }}
      </button>
    </div>

    <!-- MAIN CONTENT CONTAINER -->
    <div v-else class="event-content">
      <!-- INLINE ERROR BANNER -->
      <div v-if="errorMessage && (event || isCreateMode)" class="inline-error-message">
        {{ errorMessage }}
      </div>

      <EventDetails v-if="!isCreateMode && !isEditMode && event" :event="event" />
      <EventForm v-else-if="isCreateMode || isEditMode" ref="eventForm" :editable="true" />
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
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.header-left {
  display: flex;
  justify-content: flex-start;
}

.header-title {
  text-align: center;
}

.header-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.6rem;
}

/* Highly visible button styling */
.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.9rem;
  border-radius: 0.75rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.05);
  color: var(--color-text);
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  backdrop-filter: blur(8px);
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.back-icon {
  width: 1.1rem;
  height: 1.1rem;
  color: var(--color-primary, #14b8a6);
  transition: transform 0.2s ease;
}

.back-btn:hover {
  background: rgba(20, 184, 166, 0.12);
  border-color: rgba(20, 184, 166, 0.4);
  color: var(--color-primary, #14b8a6);
  transform: translateX(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.back-btn:active {
  transform: translateX(-1px);
}

.back-btn:focus-visible {
  outline: 2px solid var(--color-primary, #14b8a6);
  outline-offset: 2px;
}

.event-header h1 {
  margin: 0;
  font-size: clamp(1.5rem, 2.5vw, 2rem);
  font-weight: 700;
  line-height: 1.2;
  white-space: nowrap;
}

.action-btn {
  border: none;
  border-radius: 0.6rem;
  padding: 0.6rem 1.1rem;
  color: var(--color-text);
  cursor: pointer;
  font: inherit;
  font-weight: 600;
  white-space: nowrap;
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

.error-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 3.5rem 1.5rem;
  border-radius: 1rem;
  background: rgba(244, 63, 94, 0.04);
  border: 1px solid rgba(244, 63, 94, 0.2);
  margin-top: 1rem;
}

.error-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 3.25rem;
  height: 3.25rem;
  border-radius: 50%;
  background: rgba(244, 63, 94, 0.12);
  color: #f43f5e;
  margin-bottom: 1rem;
}

.error-icon {
  width: 1.75rem;
  height: 1.75rem;
}

.error-card h3 {
  margin: 0 0 0.4rem;
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--color-text);
}

.error-card p {
  margin: 0 0 1.5rem;
  color: var(--color-text-muted, #9ca3af);
  font-size: 0.95rem;
  max-width: 400px;
}

.inline-error-message {
  margin-bottom: 1.25rem;
  padding: 0.75rem 1rem;
  border-radius: 0.6rem;
  background: rgba(244, 63, 94, 0.1);
  border: 1px solid rgba(244, 63, 94, 0.2);
  color: #fda4af;
  font-size: 0.9rem;
}

@media (max-width: 568px) {
  .event-header {
    grid-template-columns: 1fr 1fr;
    grid-template-areas:
      "left right"
      "title title";
    gap: 0.85rem;
  }

  .header-left {
    grid-area: left;
  }

  .header-title {
    grid-area: title;
  }

  .header-actions {
    grid-area: right;
  }

  .event-header h1 {
    white-space: normal;
  }
}

@media (max-width: 480px) {
  .header-actions {
    width: 100%;
  }

  .action-btn {
    flex: 1;
    text-align: center;
  }
}
</style>