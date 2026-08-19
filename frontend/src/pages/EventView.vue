<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

interface EventModel {
  id: string
  name: string
  date: string
  location: string
  image: string
  type: string
  description: string
  visibility: 'public' | 'invite-only'
  invitedEmails: string[]
}

const route = useRoute()
const router = useRouter()

const fallbackEvent: EventModel = {
  id: String(route.params.id ?? 'unknown-event'),
  name: 'Event not found',
  date: '2026-01-01',
  location: 'Unknown location',
  image:
    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=80',
  type: 'Unknown',
  description: 'This event is unavailable. Open the page from a card to load real event details.',
  visibility: 'public',
  invitedEmails: [],
}

function loadEvent(): EventModel {
  const eventId = String(route.params.id ?? '')
  const key = `event:${eventId}`
  const savedEvent = sessionStorage.getItem(key)

  if (!savedEvent) {
    return { ...fallbackEvent, id: eventId || fallbackEvent.id }
  }

  try {
    const parsed = JSON.parse(savedEvent) as EventModel
    return { ...fallbackEvent, ...parsed, id: eventId || parsed.id }
  } catch {
    return { ...fallbackEvent, id: eventId || fallbackEvent.id }
  }
}

const eventData = reactive<EventModel>(loadEvent())
const formData = reactive<EventModel>({ ...eventData })
const isEditing = ref(route.query.edit === '1')
const saveMessage = ref('')
const inviteEmail = ref('')
const inviteError = ref('')

watch(
  () => route.query.edit,
  (value) => {
    isEditing.value = value === '1'
  },
)

watch(
  () => route.params.id,
  () => {
    const nextEvent = loadEvent()
    Object.assign(eventData, nextEvent)
    Object.assign(formData, nextEvent)
    saveMessage.value = ''
  },
)

const pageTitle = computed(() => (isEditing.value ? 'Edit Event' : 'Event Details'))

function startEdit() {
  saveMessage.value = ''
  inviteError.value = ''
  Object.assign(formData, eventData)
  router.replace({ query: { ...route.query, edit: '1' } })
}

function cancelEdit() {
  Object.assign(formData, eventData)
  saveMessage.value = ''
  inviteEmail.value = ''
  inviteError.value = ''
  const { edit, ...rest } = route.query
  void edit
  router.replace({ query: rest })
}

function saveChanges() {
  inviteError.value = ''
  Object.assign(eventData, formData)
  sessionStorage.setItem(`event:${eventData.id}`, JSON.stringify({ ...eventData }))
  saveMessage.value = 'Changes saved for this event.'
  inviteEmail.value = ''
  const { edit, ...rest } = route.query
  void edit
  router.replace({ query: rest })
}

function addInviteEmail() {
  const normalized = inviteEmail.value.trim().toLowerCase()
  inviteError.value = ''

  if (!normalized) {
    inviteError.value = 'Please enter an email address.'
    return
  }

  const isValidEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalized)
  if (!isValidEmail) {
    inviteError.value = 'Please enter a valid email address.'
    return
  }

  if (formData.invitedEmails.includes(normalized)) {
    inviteError.value = 'This user is already invited.'
    return
  }

  formData.invitedEmails = [...formData.invitedEmails, normalized]
  inviteEmail.value = ''
}

function removeInviteEmail(email: string) {
  formData.invitedEmails = formData.invitedEmails.filter((entry) => entry !== email)
}
</script>

<template>
  <section class="event-view-page">
    <header class="event-view-header">
      <h1>{{ pageTitle }}</h1>
      <div class="header-actions">
        <button v-if="!isEditing" type="button" class="action-btn primary" @click="startEdit">
          Edit Event
        </button>
        <template v-else>
          <button type="button" class="action-btn secondary" @click="cancelEdit">Cancel</button>
          <button type="button" class="action-btn primary" @click="saveChanges">Save</button>
        </template>
      </div>
    </header>

    <article class="event-view-card">
      <img :src="eventData.image" :alt="eventData.name" class="hero-image" />

      <div v-if="!isEditing" class="event-details">
        <p class="type-pill">{{ eventData.type }}</p>
        <h2>{{ eventData.name }}</h2>

        <div class="detail-grid">
          <div>
            <p class="label">Date</p>
            <p class="value">{{ eventData.date }}</p>
          </div>
          <div>
            <p class="label">Location</p>
            <p class="value">{{ eventData.location }}</p>
          </div>
          <div>
            <p class="label">Visibility</p>
            <p class="value visibility-value">{{ eventData.visibility }}</p>
          </div>
        </div>

        <div class="description">
          <p class="label">Description</p>
          <p class="value">{{ eventData.description }}</p>
        </div>

        <div v-if="eventData.visibility === 'invite-only'" class="invited-section">
          <p class="label">Invited Users</p>
          <ul v-if="eventData.invitedEmails.length" class="invited-list">
            <li v-for="email in eventData.invitedEmails" :key="email">{{ email }}</li>
          </ul>
          <p v-else class="value">No users invited yet.</p>
        </div>
      </div>

      <form v-else class="edit-form" @submit.prevent="saveChanges">
        <label>
          Event Name
          <input v-model="formData.name" type="text" maxlength="80" required />
        </label>

        <label>
          Type
          <input v-model="formData.type" type="text" maxlength="40" required />
        </label>

        <label>
          Date
          <input v-model="formData.date" type="text" maxlength="30" required />
        </label>

        <label>
          Location
          <input v-model="formData.location" type="text" maxlength="120" required />
        </label>

        <label>
          Image URL
          <input v-model="formData.image" type="url" maxlength="500" required />
        </label>

        <label>
          Description
          <textarea v-model="formData.description" maxlength="1200" rows="6" required />
        </label>

        <label>
          Visibility
          <select v-model="formData.visibility">
            <option value="public">Public</option>
            <option value="invite-only">Invite Only</option>
          </select>
        </label>

        <div class="invite-editor">
          <p class="invite-title">Invite users by email</p>
          <div class="invite-input-row">
            <input
              v-model="inviteEmail"
              type="email"
              maxlength="120"
              placeholder="name@example.com"
              :disabled="formData.visibility !== 'invite-only'"
            />
            <button
              type="button"
              class="invite-btn"
              :disabled="formData.visibility !== 'invite-only'"
              @click="addInviteEmail"
            >
              Add
            </button>
          </div>

          <p v-if="inviteError" class="invite-error">{{ inviteError }}</p>

          <ul v-if="formData.invitedEmails.length" class="invited-list edit-list">
            <li v-for="email in formData.invitedEmails" :key="email">
              <span>{{ email }}</span>
              <button type="button" class="remove-invite" @click="removeInviteEmail(email)">Remove</button>
            </li>
          </ul>
        </div>
      </form>
    </article>

    <p v-if="saveMessage" class="save-message">{{ saveMessage }}</p>
  </section>
</template>

<style scoped>
.event-view-page {
  width: min(100%, 1100px);
  margin: 0 auto;
  padding: 1.25rem 1.25rem 2rem;
  box-sizing: border-box;
  color: var(--color-text);
}

.event-view-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.event-view-header h1 {
  margin: 0;
  font-size: 1.55rem;
}

.header-actions {
  display: flex;
  gap: 0.6rem;
}

.action-btn {
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 0.7rem;
  padding: 0.5rem 0.95rem;
  color: var(--color-text);
  cursor: pointer;
}

.action-btn.primary {
  background: rgba(20, 184, 166, 0.18);
}

.action-btn.secondary {
  background: rgba(255, 255, 255, 0.06);
}

.event-view-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
  padding: 0.75rem;
}

.hero-image {
  width: 100%;
  aspect-ratio: 16 / 7;
  object-fit: cover;
  border-radius: 0.8rem;
  display: block;
}

.event-details {
  padding: 1rem 0.35rem 0.35rem;
}

.type-pill {
  display: inline-block;
  margin: 0 0 0.5rem;
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  background: rgba(20, 184, 166, 0.12);
  color: var(--color-primary);
  font-size: 0.68rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.event-details h2 {
  margin: 0 0 0.9rem;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.label {
  margin: 0 0 0.2rem;
  color: var(--color-text-muted);
  font-size: 0.76rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.value {
  margin: 0;
}

.visibility-value {
  text-transform: capitalize;
}

.description {
  margin-top: 1rem;
}

.invited-section {
  margin-top: 1rem;
}

.edit-form {
  padding: 1rem 0.35rem 0.35rem;
  display: grid;
  gap: 0.85rem;
}

.edit-form label {
  display: grid;
  gap: 0.35rem;
  font-weight: 600;
  font-size: 0.9rem;
}

.edit-form input,
.edit-form textarea,
.edit-form select {
  border: 1px solid rgba(255, 255, 255, 0.18);
  background: rgba(255, 255, 255, 0.05);
  color: var(--color-text);
  border-radius: 0.6rem;
  padding: 0.62rem 0.7rem;
  font: inherit;
  resize: vertical;
}

.invite-editor {
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 0.75rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.02);
}

.invite-title {
  margin: 0 0 0.55rem;
  font-size: 0.86rem;
  font-weight: 700;
}

.invite-input-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 0.55rem;
}

.invite-btn {
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: rgba(20, 184, 166, 0.18);
  color: var(--color-text);
  border-radius: 0.6rem;
  padding: 0.5rem 0.75rem;
  cursor: pointer;
}

.invite-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.invite-error {
  margin: 0.55rem 0 0;
  color: #fda4af;
  font-size: 0.8rem;
}

.invited-list {
  margin: 0.55rem 0 0;
  padding-left: 1rem;
  display: grid;
  gap: 0.3rem;
}

.edit-list {
  list-style: none;
  padding-left: 0;
}

.edit-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.8rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.6rem;
  padding: 0.45rem 0.55rem;
}

.remove-invite {
  border: 1px solid rgba(255, 255, 255, 0.16);
  background: rgba(255, 255, 255, 0.06);
  color: var(--color-text);
  border-radius: 0.5rem;
  padding: 0.3rem 0.5rem;
  cursor: pointer;
}

.save-message {
  margin: 0.9rem 0 0;
  color: var(--color-primary);
  font-weight: 600;
}

@media (max-width: 800px) {
  .event-view-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>