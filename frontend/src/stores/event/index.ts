import { defineStore } from 'pinia'
import { ref } from 'vue'
import { authRequest } from '@/services/api'
import type { User } from '@/stores/auth'
import type {
  EventModel,
  EventTypeModel,
  EventTypesResponse,
  CreateEventPayload,
  UpdateEventPayload,
  EventsResponse,
  EventResponse,
  AttendEventResponse,
  FetchEventsOptions,
} from './event.types'
export type * from './event.types'

/**
 * Normalizes incoming raw backend event data into a consistent JS format
 */
function normalizeEvent(event: any): EventModel {
  if (!event) return event
  return {
    ...event,
    public: Boolean(Number(event.public ?? event.is_public ?? 1)),
  }
}

export const useEventStore = defineStore('event', () => {
  const events = ref<EventModel[]>([])
  const eventTypes = ref<EventTypeModel[]>([])
  const currentEvent = ref<EventModel | null>(null)

  const loading = ref(false)
  const error = ref<string | null>(null)

  /**
   * Fetch all available event types for select dropdowns or filters
   */
  async function fetchEventTypes() {
    try {
      const response = await authRequest<EventTypesResponse>('/api/event-types')
      eventTypes.value = response.data
      return response.data
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load event types.'
      throw err
    }
  }

  /**
   * Unified method to fetch public, user-owned, or joined events
   * Uses authRequest so optional Bearer tokens are attached if present
   */
async function fetchEvents(options: FetchEventsOptions = {}) {
    const { 
      page = 1, 
      search = '', 
      city = '', 
      date = '', 
      event_type_id,
      per_page, 
      scope = 'public' 
    } = options

    loading.value = true
    error.value = null

    try {
      const params = new URLSearchParams()
      params.append('page', page.toString())
      params.append('scope', scope)

      if (search.trim()) params.append('search', search.trim())
      if (city.trim()) params.append('city', city.trim())
      if (date) params.append('date', date)
      if (event_type_id) params.append('event_type_id', event_type_id.toString())
      if (per_page) params.append('per_page', per_page.toString())

      const response = await authRequest<EventsResponse>(`/api/event?${params.toString()}`)
      
      const pageEvents = response.data.map(normalizeEvent)

      events.value = pageEvents

      return {
        ...response,
        data: pageEvents,
      }
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load events.'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Convenience wrapper for user's created events
  async function fetchUserEvents(page = 1) {
    return fetchEvents({ page, scope: 'own' })
  }

  // Convenience wrapper for events the user has joined
  async function fetchJoinedEvents(page = 1) {
    return fetchEvents({ page, scope: 'joined' })
  }

  async function fetchInvitedEvents(page = 1){
    return fetchEvents({ page, scope: 'invited' })
  }

  async function fetchEvent(id: number) {
    loading.value = true
    error.value = null

    try {
      const response = await authRequest<EventResponse>(`/api/event/show/${id}`)
      const event = normalizeEvent(response.data)
      currentEvent.value = event
      return event
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load event.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createEvent(payload: CreateEventPayload) {
    loading.value = true
    error.value = null

    try {
      const formData = new FormData()
      formData.append('name', payload.name)
      formData.append('date', payload.date)
      formData.append('location', payload.location)
      formData.append('event_type_id', payload.event_type_id.toString())
      formData.append('description', payload.description)
      formData.append('public', payload.public ? '1' : '0')
      formData.append('city', payload.city)

      if (payload.cover_image) {
        formData.append('cover_image', payload.cover_image)
      }

      payload.invited_emails.forEach((email) => {
        formData.append('invited_emails[]', email)
      })

      const response = await authRequest<EventResponse>('/api/event/create', {
        method: 'POST',
        body: formData,
      })

      const event = normalizeEvent(response.data)

      events.value.push(event)
      currentEvent.value = event

      return event
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to create event.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateEvent(id: number, payload: UpdateEventPayload) {
    loading.value = true
    error.value = null

    try {
      const formData = new FormData()
      formData.append('_method', 'PUT')

      Object.entries(payload).forEach(([key, value]) => {
        if (value === undefined || value === null) return
        if (key === 'invited_emails' && Array.isArray(value)) {
          value.forEach((email) => formData.append('invited_emails[]', email))
        } else if (typeof value === 'boolean') {
          formData.append(key, value ? '1' : '0')
        } else {
          formData.append(key, value as string | Blob)
        }
      })

      const response = await authRequest<EventResponse>(`/api/event/update/${id}`, {
        method: 'POST',
        body: formData,
      })

      const event = normalizeEvent(response.data)

      // Update in all local lists where it might exist
      const updateList = (list: EventModel[]) => {
        const idx = list.findIndex((item) => Number(item.id) === id)
        if (idx !== -1) list[idx] = event
      }

      updateList(events.value)

      if (Number(currentEvent.value?.id) === id) {
        currentEvent.value = event
      }

      return event
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to update event.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteEvent(id: number) {
    loading.value = true
    error.value = null

    try {
      await authRequest<unknown>(`/api/event/delete/${id}`, {
        method: 'DELETE',
        body: JSON.stringify({ id }),
      })

      const filterOut = (list: EventModel[]) => list.filter((e) => e.id !== id)

      events.value = filterOut(events.value)

      if (currentEvent.value?.id === id) {
        currentEvent.value = null
      }
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to delete event.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function joinEvent(id: number) {
    loading.value = true
    error.value = null

    try {
      const response = await authRequest<AttendEventResponse>(
        `/api/event/attend/${id}`,
        { method: 'POST' }
      )

      const updatedEvent = normalizeEvent(response.event)

      const updateList = (list: EventModel[]) => {
        const idx = list.findIndex((item) => Number(item.id) === id)
        if (idx !== -1) list[idx] = updatedEvent
      }

      updateList(events.value)

      if (Number(currentEvent.value?.id) === id) {
        currentEvent.value = updatedEvent
      }

      return {
        ...response,
        event: updatedEvent,
      }
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to update attendance status.'
      throw err
    } finally {
      loading.value = false
    }
  }
  async function validateInvite(email: string): Promise<boolean> {
    try {
      const response = await authRequest<{ exists: boolean }>('/api/user/validate-email', {
        method: 'POST',
        body: JSON.stringify({ email }),
      })
      return Boolean(response.exists)
    } catch {
      return false
    }
  }
  function clearError() {
    error.value = null
  }

  function clearCurrentEvent() {
    currentEvent.value = null
  }

  return {
    events,
    eventTypes,
    currentEvent,
    loading,
    error,
    fetchEventTypes,
    fetchEvents,
    fetchUserEvents,
    fetchJoinedEvents,
    fetchInvitedEvents,
    fetchEvent,
    createEvent,
    updateEvent,
    deleteEvent,
    joinEvent,
    clearError,
    clearCurrentEvent,
    validateInvite,
  }
})