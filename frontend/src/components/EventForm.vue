<script setup lang="ts">
import type {
  EventModel,
  CreateEventPayload,
  UpdateEventPayload,
} from '@/stores/event'

import { reactive, ref } from 'vue'

const defaultEvent: EventModel = {
  id: -1,
  name: '',
  date: '',
  location: '',
  city: '',
  cover_image: '',
  type: '',
  user_id: 0,
  description: '',
  visibility: 'public',
  invitedEmails: [],
  owner:null,
}

const formData = reactive({
  id: '',
  name: '',
  date: '',
  city: '',
  location: '',
  type: '',
  description: '',
  visibility: 'public' as EventModel['visibility'],
  invitedEmails: [] as string[],

  // Image URL/path currently displayed.
  // This can be an existing backend image or a local preview.
  image: '',

  // Actual newly selected image file.
  imageFile: null as File | null,
})

const inviteEmail = ref('')
const inviteError = ref('')
const imageError = ref('')
const imageInput = ref<HTMLInputElement | null>(null)

/**
 * Load an existing event into the form.
 *
 * EventView can call:
 *
 * eventForm.value?.load(event)
 */
function load(event: EventModel) {
  Object.assign(formData, {
    id: event.id,
    name: event.name,
    date: event.date,
    city: event.city,
    location: event.location,
    type: event.type,
    description: event.description,
    visibility: event.visibility,
    invitedEmails: [event.invitedEmails],

    // Existing image from backend.
    image: event.cover_image,

    // No new file selected yet.
    imageFile: null,
  })

  inviteEmail.value = ''
  inviteError.value = ''
  imageError.value = ''

  if (imageInput.value) {
    imageInput.value.value = ''
  }
}

/**
 * Get a complete payload for creating an event.
 */
function get(): CreateEventPayload {
  return {
    name: formData.name,
    date: formData.date,
    city: formData.city,
    location: formData.location,
    type: formData.type,
    description: formData.description,
    visibility: formData.visibility,
    invitedEmails: [...formData.invitedEmails],

    // Actual File object.
    cover_image: formData.imageFile,
  }
}

/**
 * Get the payload for updating an event.
 *
 * image is null when the user did not select a new image.
 */
function getUpdatePayload(): UpdateEventPayload {
  return {
    name: formData.name,
    date: formData.date,
    city: formData.city,
    location: formData.location,
    type: formData.type,
    description: formData.description,
    visibility: formData.visibility,
    invitedEmails: [...formData.invitedEmails],
    cover_image: formData.imageFile,
  }
}

/**
 * Handle image upload.
 */
function handleImageUpload(event: Event) {
  imageError.value = ''

  const input = event.target as HTMLInputElement
  const file = input.files?.[0]

  if (!file) {
    return
  }

  if (!file.type.startsWith('image/')) {
    imageError.value = 'Please select an image file.'
    input.value = ''
    return
  }

  const maxSize = 5 * 1024 * 1024

  if (file.size > maxSize) {
    imageError.value = 'The image must be smaller than 5 MB.'
    input.value = ''
    return
  }

  // Store the actual file for the API request.
  formData.imageFile = file

  // Create a temporary browser preview.
  formData.image = URL.createObjectURL(file)
}

/**
 * Remove the current image.
 */
function removeImage() {
  formData.image = ''
  formData.imageFile = null
  imageError.value = ''

  if (imageInput.value) {
    imageInput.value.value = ''
  }
}

/**
 * Add an invited email.
 */
function addInviteEmail() {
  const normalized = inviteEmail.value.trim().toLowerCase()

  inviteError.value = ''

  if (!normalized) {
    inviteError.value = 'Please enter an email address.'
    return
  }

  const isValidEmail =
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalized)

  if (!isValidEmail) {
    inviteError.value = 'Please enter a valid email address.'
    return
  }

  if (formData.invitedEmails.includes(normalized)) {
    inviteError.value = 'This user is already invited.'
    return
  }

  formData.invitedEmails.push(normalized)
  inviteEmail.value = ''
}

/**
 * Remove an invited email.
 */
function removeInviteEmail(email: string) {
  formData.invitedEmails =
    formData.invitedEmails.filter(
      (entry) => entry !== email,
    )
}

/**
 * Reset the form.
 */
function reset() {
  load(defaultEvent)
}

defineExpose({
  load,
  get,
  getUpdatePayload,
  reset,
})
</script>

<template>
  <form
    class="edit-form"
    @submit.prevent
  >
    <!-- Event name -->
    <label>
      Event Name

      <input
        v-model="formData.name"
        type="text"
        maxlength="80"
        required
        placeholder="Enter event name"
      />
    </label>

    <!-- Event type -->
    <label>
      Type

      <input
        v-model="formData.type"
        type="text"
        maxlength="40"
        required
        placeholder="Concert, party, meetup..."
      />
    </label>

    <!-- Date -->
    <label>
      Date

      <input
        v-model="formData.date"
        type="date"
        required
      />
    </label>

    <!-- Location -->
    <label>
      Location

      <input
        v-model="formData.location"
        type="text"
        maxlength="120"
        required
        placeholder="Street, venue, address..."
      />
    </label>

    <!-- City -->
    <label>
      City

      <input
        v-model="formData.city"
        type="text"
        maxlength="100"
        required
        placeholder="e.g. Szeged"
      />
    </label>

    <!-- Image -->
    <div class="image-field">
      <label>
        Event Image

        <input
          ref="imageInput"
          type="file"
          accept="image/*"
          @change="handleImageUpload"
        />
      </label>

      <p
        v-if="imageError"
        class="image-error"
      >
        {{ imageError }}
      </p>

      <!-- Image preview -->
      <div
        v-if="formData.image"
        class="image-preview"
      >
        <img
          :src="formData.image"
          :alt="formData.name || 'Event preview'"
        />

        <div class="image-overlay">
          <button
            type="button"
            class="remove-image"
            @click="removeImage"
          >
            Remove Image
          </button>
        </div>
      </div>

      <!-- Empty image placeholder -->
      <div
        v-else
        class="image-placeholder"
      >
        <span>Upload an event image</span>
        <small>PNG, JPG, WEBP — max 5 MB</small>
      </div>
    </div>

    <!-- Description -->
    <label>
      Description

      <textarea
        v-model="formData.description"
        maxlength="1200"
        rows="6"
        required
        placeholder="Describe your event..."
      />
    </label>

    <!-- Visibility -->
    <label>
      Visibility

      <select v-model="formData.visibility">
        <option value="public">
          Public
        </option>

        <option value="invite-only">
          Invite Only
        </option>
      </select>
    </label>

    <!-- Invitations -->
    <div class="invite-editor">
      <p class="invite-title">
        Invite users by email
      </p>

      <div class="invite-input-row">
        <input
          v-model="inviteEmail"
          type="email"
          maxlength="120"
          placeholder="name@example.com"
          :disabled="
            formData.visibility !== 'invite-only'
          "
          @keyup.enter.prevent="addInviteEmail"
        />

        <button
          type="button"
          class="invite-btn"
          :disabled="
            formData.visibility !== 'invite-only'
          "
          @click="addInviteEmail"
        >
          Add
        </button>
      </div>

      <p
        v-if="inviteError"
        class="invite-error"
      >
        {{ inviteError }}
      </p>

      <ul
        v-if="formData.invitedEmails.length"
        class="invited-list"
      >
        <li
          v-for="email in formData.invitedEmails"
          :key="email"
        >
          <span>{{ email }}</span>

          <button
            type="button"
            class="remove-invite"
            @click="removeInviteEmail(email)"
          >
            Remove
          </button>
        </li>
      </ul>
    </div>
  </form>
</template>

<style scoped>
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
}

.edit-form textarea {
  resize: vertical;
}

.edit-form input:focus,
.edit-form textarea:focus,
.edit-form select:focus {
  outline: 2px solid rgba(20, 184, 166, 0.45);
  outline-offset: 1px;
}

/* Image */

.image-field {
  display: grid;
  gap: 0.5rem;
}

.image-field > label {
  display: grid;
  gap: 0.35rem;
}

.image-field input[type='file'] {
  padding: 0.55rem;
  cursor: pointer;
}

.image-preview,
.image-placeholder {
  width: 100%;
  aspect-ratio: 16 / 7;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.8rem;
  background: rgba(255, 255, 255, 0.03);
}

.image-preview {
  position: relative;
}

.image-preview img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-overlay {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  justify-content: flex-end;
  padding: 0.75rem;
  background: linear-gradient(
    transparent,
    rgba(0, 0, 0, 0.65)
  );
}

.remove-image {
  border: 1px solid rgba(255, 255, 255, 0.16);
  background: rgba(0, 0, 0, 0.45);
  color: var(--color-text);
  border-radius: 0.5rem;
  padding: 0.4rem 0.65rem;
  cursor: pointer;
  font: inherit;
}

.remove-image:hover {
  background: rgba(0, 0, 0, 0.65);
}

.image-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  color: var(--color-text-muted);
  border-style: dashed;
}

.image-placeholder span {
  font-weight: 600;
}

.image-placeholder small {
  font-size: 0.75rem;
}

.image-error {
  margin: 0;
  color: #fda4af;
  font-size: 0.8rem;
}

/* Invitations */

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
  font: inherit;
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
  padding: 0;
  list-style: none;
  display: grid;
  gap: 0.3rem;
}

.invited-list li {
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
  font: inherit;
}

.remove-invite:hover {
  background: rgba(255, 255, 255, 0.1);
}
</style>