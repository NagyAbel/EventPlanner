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
  visibility: 'public' | 'invite-only'
  invitedEmails: string[]
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
  visibility: 'public' | 'invite-only'
  invitedEmails: string[]
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


export const useEventStore = defineStore('event', () => {
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

      if (page === 1) {
        events.value = pageEvents
      } else {
        events.value.push(...pageEvents)
      }

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
        formData.append('visibility', payload.visibility)
        formData.append('city', payload.city)
        if (payload.cover_image) {
            formData.append('cover_image', payload.cover_image)
        }   

        payload.invitedEmails.forEach((email) => {
            formData.append('invitedEmails[]', email)
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
  async function updateEvent(id: number,payload: UpdateEventPayload,) {
    loading.value = true
    error.value = null

   try {
      const formData = new FormData()
      formData.append('_method', 'PUT')

      if (payload.name !== undefined) {
        formData.append('name', payload.name)
      }

      if (payload.date !== undefined) {
        formData.append('date', payload.date)
      }

      if (payload.city !== undefined) {
        formData.append('city', payload.city)
      }

      if (payload.location !== undefined) {
        formData.append('location', payload.location)
      }

      if (payload.type !== undefined) {
        formData.append('type', payload.type)
      }

      if (payload.description !== undefined) {
        formData.append('description', payload.description)
      }

      if (payload.visibility !== undefined) {
        formData.append('visibility', payload.visibility)
      }

      if (payload.cover_image instanceof File) {
        formData.append('cover_image', payload.cover_image)
      }

      if (payload.invitedEmails !== undefined) {
        payload.invitedEmails.forEach((email) => {
          formData.append('invitedEmails[]', email)
        })
      }

      const response = await authRequest<EventResponse>(
        `/api/event/update/${id}`,
        {
          method: 'POST',
          body: formData,
        },
      )

      const event = response.data

      const index = events.value.findIndex(
        (item) => Number(item.id) === id,
      )

      if (index !== -1) {
        events.value[index] = event
      }

      if (Number(currentEvent.value?.id) === id) {
        currentEvent.value = event
      }

      return event
    } catch (err) {
      error.value =
        err instanceof Error
          ? err.message
          : 'Failed to update event.'

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
        '/api/event/delete',
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

  function clearError() {
    error.value = null
  }

  function clearCurrentEvent() {
    currentEvent.value = null
  }

  return {
    events,
    currentEvent,
    loading,
    error,

    fetchUserEvents,
    fetchEvent,
    createEvent,
    updateEvent,
    deleteEvent,

    clearError,
    clearCurrentEvent,
  }
})