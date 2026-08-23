import type { User import { defineStore } from 'pinia'
import { ref } from 'vue'
import { authRequest } from '@/services/api'
import type {
  EventModel,
  CreateEventPayload,
  UpdateEventPayload,
  EventsResponse,
  EventResponse,
  AttendEventResponse,
  FetchEventsOptions,
} from './event.types'} from '@/stores/auth'

export interface EventModel {
  id: number
  name: string
  date: string
  city: string
  location: string
  cover_image: string
  type: string
  user_id: number
  description: string | null
  public: boolean
  is_attending: boolean
  attendee_count: number
  invited_emails: string[]
  owner: User | null
}

export interface CreateEventPayload {
  name: string
  date: string
  city: string
  location: string
  cover_image: File | null
  type: string
  description: string
  public: boolean
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

export interface EventResponse {
  data: EventModel
  message?: string
}

export interface AttendEventResponse {
  message: string
  attending: boolean
  event: EventModel
}

export type EventScope = 'public' | 'own' | 'joined' | 'invited'

export interface FetchEventsOptions {
  page?: number
  search?: string
  city?: string
  date?: string
  per_page?: number
  scope?: EventScope
}