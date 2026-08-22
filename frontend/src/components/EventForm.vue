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
  public: true,
  invited_emails: [],
  owner: null,
}

const formData = reactive({
  id: '',
  name: '',
  date: '',
  city: '',
  location: '',
  type: '',
  description: '',
  public: true as EventModel['public'],
  invited_emails: [] as string[],

  // Image URL/path currently displayed.
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
 */
function load(event: EventModel) {
  // Normalize invited_emails to avoid nested arrays like [["email"]]
  const emails = Array.isArray(event.invited_emails) 
    ? event.invited_emails.flat() 
    : []

  Object.assign(formData, {
    id: event.id,
    name: event.name,
    date: event.date,
    city: event.city,
    location: event.location,
    type: event.type,
    description: event.description,
    public: Boolean(Number(event.public)),    
  invited_emails: [...emails],

    // Existing image from backend
    image: event.cover_image,

    // No new file selected yet
    imageFile: null,
  })

  inviteEmail.value = ''
  inviteError.value = ''
  imageError.value = ''

  if (imageInput.value) {
    imageInput.value.value = ''
  }
}

function triggerFileInput() {
  imageInput.value?.click()
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
    public: formData.public,
    invited_emails: [...formData.invited_emails],
    cover_image: formData.imageFile,
  }
}

/**
 * Get the payload for updating an event.
 */
function getUpdatePayload(): UpdateEventPayload {
  return {
    name: formData.name,
    date: formData.date,
    city: formData.city,
    location: formData.location,
    type: formData.type,
    description: formData.description,
    public: formData.public,
    invited_emails: [...formData.invited_emails],
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

  if (!file) return

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

  formData.imageFile = file
  formData.image = URL.createObjectURL(file)
}

/**
 * Remove the current image.
 */
function removeImage(e?: Event) {
  if (e) e.stopPropagation()
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

  const isValidEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalized)

  if (!isValidEmail) {
    inviteError.value = 'Please enter a valid email address.'
    return
  }

  if (formData.invited_emails.includes(normalized)) {
    inviteError.value = 'This user is already invited.'
    return
  }

  formData.invited_emails.push(normalized)
  inviteEmail.value = ''
}

/**
 * Remove an invited email.
 */
function removeInviteEmail(email: string) {
  formData.invited_emails = formData.invited_emails.filter(
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
  <form class="edit-form" @submit.prevent>
    <!-- Event name -->
    <label class="form-group">
      <span class="label-text">Event Name</span>
      <input
        v-model="formData.name"
        type="text"
        maxlength="80"
        required
        placeholder="Enter event name"
      />
    </label>

    <!-- Event type -->
    <label class="form-group">
      <span class="label-text">Type</span>
      <input
        v-model="formData.type"
        type="text"
        maxlength="40"
        required
        placeholder="Concert, party, meetup..."
      />
    </label>

    <!-- Date -->
    <label class="form-group">
      <span class="label-text">Date</span>
      <input v-model="formData.date" type="date" required />
    </label>

    <!-- Grid row for Location & City -->
    <div class="form-row">
      <label class="form-group">
        <span class="label-text">Location</span>
        <input
          v-model="formData.location"
          type="text"
          maxlength="120"
          required
          placeholder="Street, venue, address..."
        />
      </label>

      <label class="form-group">
        <span class="label-text">City</span>
        <input
          v-model="formData.city"
          type="text"
          maxlength="100"
          required
          placeholder="e.g. Szeged"
        />
      </label>
    </div>

    <!-- Custom Image Upload Dropzone -->
    <div class="form-group">
      <span class="label-text">Event Cover Image</span>

      <input
        ref="imageInput"
        type="file"
        accept="image/*"
        class="hidden-file-input"
        @change="handleImageUpload"
      />

      <!-- Image Preview State -->
      <div v-if="formData.image" class="image-preview">
        <img :src="formData.image" :alt="formData.name || 'Event preview'" />
        <div class="image-overlay">
          <button type="button" class="btn-subtle" @click="triggerFileInput">
            Change
          </button>
          <button type="button" class="btn-subtle danger" @click="removeImage">
            Remove
          </button>
        </div>
      </div>

      <!-- Upload Dropzone Placeholder State -->
      <div v-else class="upload-dropzone" @click="triggerFileInput">
        <svg
          class="upload-icon"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
          />
        </svg>
        <span class="upload-title">Click to upload cover image</span>
        <span class="upload-hint">PNG, JPG, or WEBP up to 5MB</span>
      </div>

      <p v-if="imageError" class="field-error">{{ imageError }}</p>
    </div>

    <!-- Description -->
    <label class="form-group">
      <span class="label-text">Description</span>
      <textarea
        v-model="formData.description"
        maxlength="1200"
        rows="5"
        required
        placeholder="Describe your event..."
      />
    </label>

    <!-- Visibility -->
    <label class="form-group">
      <span class="label-text">Visibility</span>
      <select v-model="formData.public" class="visibility-select">
        <option :value="true">Public</option>
        <option :value="false">Invite Only</option>
      </select>
    </label>

    <!-- Invitations -->
    <div class="invite-editor">
      <span class="label-text">Invite Users by Email</span>

      <div class="invite-input-row">
        <input
          v-model="inviteEmail"
          type="email"
          maxlength="120"
          placeholder="name@example.com"
          @keyup.enter.prevent="addInviteEmail"
        />

        <button type="button" class="btn-add" @click="addInviteEmail">
          Add
        </button>
      </div>

      <p v-if="inviteError" class="field-error">{{ inviteError }}</p>

      <ul v-if="formData.invited_emails.length" class="invited-tags">
        <li
          v-for="email in formData.invited_emails"
          :key="email"
          class="email-tag"
        >
          <span>{{ email }}</span>
          <button
            type="button"
            class="tag-remove"
            title="Remove email"
            @click="removeInviteEmail(email)"
          >
            &times;
          </button>
        </li>
      </ul>
    </div>
  </form>
</template>

<style scoped>
.edit-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.label-text {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text);
}

.edit-form input[type='text'],
.edit-form input[type='date'],
.edit-form input[type='email'],
.edit-form textarea,
.edit-form select {
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.04);
  color: var(--color-text);
  border-radius: 0.6rem;
  padding: 0.65rem 0.8rem;
  font: inherit;
  font-size: 0.95rem;
  transition: border-color 0.15s ease;
}

.edit-form textarea {
  resize: vertical;
}

.edit-form input:focus,
.edit-form textarea:focus,
.edit-form select:focus {
  outline: none;
  border-color: var(--color-primary, #14b8a6);
}

/* CUSTOM FILE UPLOAD DROPZONE */
.hidden-file-input {
  display: none;
}

.upload-dropzone {
  width: 100%;
  aspect-ratio: 21 / 9;
  border: 2px dashed rgba(255, 255, 255, 0.15);
  border-radius: 0.8rem;
  background: rgba(255, 255, 255, 0.02);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  cursor: pointer;
  transition: border-color 0.15s ease, background-color 0.15s ease;
  padding: 1rem;
  box-sizing: border-box;
}

.upload-dropzone:hover {
  border-color: var(--color-primary, #14b8a6);
  background: rgba(20, 184, 166, 0.04);
}

.upload-icon {
  width: 32px;
  height: 32px;
  color: var(--color-text-muted, #9ca3af);
}

.upload-title {
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--color-text);
}

.upload-hint {
  font-size: 0.75rem;
  color: var(--color-text-muted, #9ca3af);
}

.image-preview {
  position: relative;
  width: 100%;
  aspect-ratio: 21 / 9;
  border-radius: 0.8rem;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  opacity: 0;
  transition: opacity 0.15s ease;
}

.image-preview:hover .image-overlay {
  opacity: 1;
}

.btn-subtle {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: #fff;
  padding: 0.4rem 0.8rem;
  border-radius: 0.5rem;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
}

.btn-subtle.danger {
  background: rgba(244, 63, 94, 0.8);
}
.edit-form select option {
  background: #222;
  color: #fff;
}
/* INVITATIONS & TAGS */
.invite-editor {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.invite-input-row {
  display: flex;
  gap: 0.5rem;
}

.invite-input-row input {
  flex: 1;
}

.btn-add {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: var(--color-text);
  border-radius: 0.6rem;
  padding: 0 1.25rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-add:hover {
  background: rgba(255, 255, 255, 0.15);
}

.invited-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  list-style: none;
  margin: 0.25rem 0 0;
  padding: 0;
}

.email-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.3rem 0.6rem;
  border-radius: 0.4rem;
  font-size: 0.85rem;
  color: var(--color-text);
}

.tag-remove {
  background: transparent;
  border: none;
  color: var(--color-text-muted, #9ca3af);
  font-size: 1.1rem;
  line-height: 1;
  padding: 0;
  cursor: pointer;
}

.tag-remove:hover {
  color: #fda4af;
}

.field-error {
  margin: 0;
  color: #fda4af;
  font-size: 0.8rem;
}

@media (max-width: 600px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>