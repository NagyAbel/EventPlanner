import { defineStore } from 'pinia'
import { ref } from 'vue'
import { apiRequest, authRequest } from '@/services/api'
import type { User } from '@/stores/auth'
export interface EventModel {
  id: number
  name: string
  date: string
  city: string
  location:string
  cover_image: string
  type: string
  user_id:number
  description: string
  public: boolean
  is_attending: boolean
  invited_emails: string[]
  owner:User | null
}

export interface CreateEventPayload {
  name: string
  date: string
  city:string
  location: string
  cover_image: File | null
  type: string
  description: string
  public:boolean
  invited_emails: string[]
}

export type UpdateEventPayload = Partial<CreateEventPayload>
export interface PaginatedEvents {
  data: EventModel[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export type EventsResponse = PaginatedEvents

interface EventResponse {
  data: EventModel
  message?: string
}
export interface AttendEventResponse {
  message: string
  attending: boolean
  event: EventModel
}
export interface FetchEventsOptions {
  page?: number
  search?: string
  city?: string
  date?: string
  per_page?: number
}
export const useEventStore = defineStore('event', () => {
  const userEvents = ref<EventModel[]>([])
  const joinedEvents = ref<EventModel[]>([])

  const events = ref<EventModel[]>([])

  const currentEvent = ref<EventModel | null>(null)

  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchUserEvents(page = 1) {
    loading.value = true
    error.value = null

    try {
      const response = await apiRequest<EventsResponse>(
        `/api/event/own?page=${page}`,
      )

      const pageEvents = response.data

      userEvents.value = pageEvents
    return response
    } catch (err) {
      error.value =
        err instanceof Error
          ? err.message
          : 'Failed to load events.'

      throw err
   } finally {
      loading.value = false
    }
  }
  async function fetchJoinedEvents(page = 1) {
    loading.value = true
    error.value = null

    try {
      const response = await authRequest<EventsResponse>(
        `/api/event/joined?page=${page}`
     )

      const pageEvents = response.data

      // Directly assign page events to support page-based navigation
      joinedEvents.value = pageEvents

      return response
    } catch (err) {
      error.value = err instanceof Error? err.message : 'Failed to load joined events.'

      throw err
    } finally {
      loading.value = false
    }
  }
  async function fetchEvents(options: FetchEventsOptions = {}) {
    const { page = 1, search = '', city = '', date = '', per_page } = options
    loading.value = true
    error.value = null

    try {
      const params = new URLSearchParams()
      params.append('page', page.toString())

      if (search.trim()) params.append('search', search.trim())
      if (city.trim()) params.append('city', city.trim())
      if (date) params.append('date', date)
      if (per_page) params.append('per_page', per_page.toString())

      const response = await apiRequest<EventsResponse>(
        `/api/event/list?${params.toString()}`
      )

      const pageEvents = response.data

      
      events.value = pageEvents
      
      return response
    } catch (err) {
        error.value = err instanceof Error? err.message: 'Failed to load events.'

        throw err
    } finally {
      loading.value = false
    }
  }
  async function fetchEvent(id: number) {
    loading.value = true
    error.value = null

    try {
      const response = await apiRequest<EventResponse>(
        `/api/event/show/${id}`,
      )

      currentEvent.value = response.data

      return response.data
    } catch (err) {
      error.value =
        err instanceof Error
          ? err.message
          : 'Failed to load event.'

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
        formData.append('type', payload.type)
        formData.append('description', payload.description)
        formData.append('public', payload.public ? '1' : '0')
        formData.append('city', payload.city)
        if (payload.cover_image) {
            formData.append('cover_image', payload.cover_image)
        }   

        payload.invited_emails.forEach((email) => {
            formData.append('invited_emails[]', email)
        })

        const response = await authRequest<EventResponse>(
        '/api/event/create',
        {
            method: 'POST',
            body: formData,
        },
        )

        const event = response.data

        events.value.push(event)
        currentEvent.value = event

        return event
    } catch (err) {
        error.value = err instanceof Error
        ? err.message
        : 'Failed to create event.'

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

      const event = response.data

      const index = events.value.findIndex((item) => Number(item.id) === id)
      if (index !== -1) events.value[index] = event
      if (Number(currentEvent.value?.id) === id) currentEvent.value = event

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
      await authRequest<unknown>(
        `/api/event/delete/${id}`,
        {
          method: 'DELETE',
          body: JSON.stringify({ id }),
        },
      )

      events.value = events.value.filter(
        (event) => event.id !== id,
      )

      if (currentEvent.value?.id === id) {
        currentEvent.value = null
      }
    } catch (err) {
      error.value =
        err instanceof Error
          ? err.message
          : 'Failed to delete event.'

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
        `/api/event/attend/${id}`,{method: 'POST',},
      )

      const updatedEvent = response.event

      const index = events.value.findIndex((item) => Number(item.id) === id)
      if (index !== -1) {
        events.value[index] = updatedEvent
      }

      if (Number(currentEvent.value?.id) === id) {
        currentEvent.value = updatedEvent
      }

      const joinedEventIndex = joinedEvents.value.findIndex((item) => Number(item.id) === id)
      if (response.attending) {
        if (joinedEventIndex === -1) {
          joinedEvents.value.push(updatedEvent)
        } else {
          joinedEvents.value[joinedEventIndex] = updatedEvent
        }
      } else if (joinedEventIndex !== -1) {
        joinedEvents.value.splice(joinedEventIndex, 1)
      }

      return response
    } catch (err) {
      error.value =
        err instanceof Error ? err.message : 'Failed to update attendance status.'
        throw err
    } finally {
      loading.value = false
    }
  }
  function clearError() {
    error.value = null
  }

  function clearCurrentEvent() {
    currentEvent.value = null
  }

  return {
    joinedEvents,
    userEvents,
    events,
    currentEvent,
    loading,
    error,
    fetchUserEvents,
    fetchJoinedEvents,
    fetchEvents,
    fetchEvent,
    createEvent,
    updateEvent,
    deleteEvent,
    joinEvent,

    clearError,
    clearCurrentEvent,
  }
})