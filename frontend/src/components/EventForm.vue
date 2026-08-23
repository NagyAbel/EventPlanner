<script setup lang="ts">
import type {
  EventModel,
  CreateEventPayload,
  UpdateEventPayload,
} from '@/stores/event'

import { reactive, ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useEventStore } from '@/stores/event'

const { t } = useI18n()
const eventStore = useEventStore()

const formData = reactive({
  id: 0,
  name: '',
  date: '',
  city: '',
  location: '',
  event_type_id: null as number | null,
  description: '',
  public: true,
  invited_emails: [] as string[],
  image: '',
  imageFile: null as File | null,
})

// Combobox / Searchable Select states
const typeSearchQuery = ref('')
const isTypeDropdownOpen = ref(false)

const filteredEventTypes = computed(() => {
  const query = typeSearchQuery.value.trim().toLowerCase()
  if (!query) return eventStore.eventTypes
  return eventStore.eventTypes.filter((type) =>
    type.name.toLowerCase().includes(query)
  )
})

const selectedTypeName = computed(() => {
  const found = eventStore.eventTypes.find((t) => t.id === formData.event_type_id)
  return found ? found.name : ''
})

// Validation errors
const errors = reactive<Record<string, string>>({})
const inviteEmail = ref('')
const inviteError = ref('')
const imageError = ref('')
const imageInput = ref<HTMLInputElement | null>(null)

onMounted(async () => {
  if (eventStore.eventTypes.length === 0) {
    await eventStore.fetchEventTypes()
  }
})

function validate(): boolean {
  Object.keys(errors).forEach((key) => delete errors[key])
  imageError.value = ''

  if (!formData.name.trim()) errors.name = t('eventForm.nameRequired')
  if (!formData.event_type_id) errors.event_type_id = t('eventForm.typeRequired')
  if (!formData.date) errors.date = t('eventForm.dateRequired')
  if (!formData.location.trim()) errors.location = t('eventForm.locationRequired')
  if (!formData.city.trim()) errors.city = t('eventForm.cityRequired')
  if (!formData.description) errors.description = t('eventForm.descriptionRequired')
  if (!formData.image && !formData.imageFile) imageError.value = t('eventForm.imageRequired')

  return Object.keys(errors).length === 0 && !imageError.value
}

/**
 * Load event or reset form if null/undefined is passed
 */
function load(event?: Partial<EventModel> | null) {
  const isEdit = Boolean(event?.id)
  console.log(event?.public)
  Object.assign(formData, {
    id: event?.id ?? 0,
    name: event?.name ?? '',
    date: event?.date ?? '',
    city: event?.city ?? '',
    location: event?.location ?? '',
    event_type_id: event?.type?.id ?? null,
    description: event?.description ?? '',
    public: event?.public ?? true,
    invited_emails: Array.isArray(event?.invited_emails) ? [...event.invited_emails.flat()] : [],
    image: event?.cover_image ?? '',
    imageFile: null,
  })

  typeSearchQuery.value = isEdit ? (event?.type?.name ?? '') : ''

  // Clear validation state
  Object.keys(errors).forEach((key) => delete errors[key])
  inviteEmail.value = ''
  inviteError.value = ''
  imageError.value = ''
  if (imageInput.value) imageInput.value.value = ''
}

function selectType(type: { id: number; name: string }) {
  formData.event_type_id = type.id
  typeSearchQuery.value = type.name
  isTypeDropdownOpen.value = false
  delete errors.event_type_id
}

function handleTypeBlur() {
  setTimeout(() => {
    isTypeDropdownOpen.value = false
    typeSearchQuery.value = selectedTypeName.value
    if (!selectedTypeName.value) {
      formData.event_type_id = null
    }
  }, 200)
}

function triggerFileInput() {
  imageInput.value?.click()
}

function buildPayload(): CreateEventPayload {
  return {
    name: formData.name,
    date: formData.date,
    city: formData.city,
    location: formData.location,
    event_type_id: formData.event_type_id!,
    description: formData.description,
    public: formData.public,
    invited_emails: [...formData.invited_emails],
    cover_image: formData.imageFile,
  }
}

// Alias getters to share identical structure
const get = buildPayload
const getUpdatePayload = buildPayload as () => UpdateEventPayload

function handleImageUpload(e: Event) {
  imageError.value = ''
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]

  if (!file) return

  if (!file.type.startsWith('image/')) {
    imageError.value = t('eventForm.imageTypeError')
    input.value = ''
    return
  }

  if (file.size > 5 * 1024 * 1024) {
    imageError.value = t('eventForm.imageSizeError')
    input.value = ''
    return
  }

  formData.imageFile = file
  formData.image = URL.createObjectURL(file)
}

function removeImage(e?: Event) {
  e?.stopPropagation()
  formData.image = ''
  formData.imageFile = null
  imageError.value = ''
  if (imageInput.value) imageInput.value.value = ''
}

function addInviteEmail() {
  if (formData.public) return
  const normalized = inviteEmail.value.trim().toLowerCase()
  inviteError.value = ''

  if (!normalized) {
    inviteError.value = t('eventForm.inviteEmailRequired')
    return
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalized)) {
    inviteError.value = t('eventForm.inviteEmailInvalid')
    return
  }

  if (formData.invited_emails.includes(normalized)) {
    inviteError.value = t('eventForm.inviteEmailDuplicate')
    return
  }

  formData.invited_emails.push(normalized)
  inviteEmail.value = ''
}

function removeInviteEmail(email: string) {
  if (formData.public) return
  formData.invited_emails = formData.invited_emails.filter((entry) => entry !== email)
}

function reset() {
  load(null)
}

defineExpose({
  load,
  get,
  getUpdatePayload,
  reset,
  validate,
})
</script>
<template>
  <form class="edit-form" novalidate @submit.prevent>
    <!-- Event name -->
    <label class="form-group">
      <span class="label-text">{{ t('eventForm.eventName') }}</span>
      <input
        v-model="formData.name"
        type="text"
        maxlength="80"
        :class="{ 'has-error': errors.name }"
        :placeholder="t('eventForm.eventNamePlaceholder')"
      />
      <p v-if="errors.name" class="field-error">{{ errors.name }}</p>
    </label>

    <!-- Event type (Searchable Combobox) -->
    <div class="form-group combobox-group">
      <span class="label-text">{{ t('eventForm.type') }}</span>
      <div class="combobox-wrapper">
        <input
          v-model="typeSearchQuery"
          type="text"
          :class="{ 'has-error': errors.event_type_id }"
          :placeholder="t('eventForm.typePlaceholder')"
          @focus="isTypeDropdownOpen = true"
          @blur="handleTypeBlur"
        />
        <ul v-if="isTypeDropdownOpen" class="combobox-dropdown">
          <li
            v-for="type in filteredEventTypes"
            :key="type.id"
            :class="{ 'is-selected': type.id === formData.event_type_id }"
            @mousedown.prevent="selectType(type)"
          >
            {{ type.name }}
          </li>
          <li v-if="filteredEventTypes.length === 0" class="no-results">
            No types found
          </li>
        </ul>
      </div>
      <p v-if="errors.event_type_id" class="field-error">{{ errors.event_type_id }}</p>
    </div>

    <!-- Date & Time -->
    <label class="form-group">
      <span class="label-text">{{ t('eventForm.date') }}</span>
      <input
        v-model="formData.date"
        type="datetime-local"
        :class="{ 'has-error': errors.date }"
      />
      <p v-if="errors.date" class="field-error">{{ errors.date }}</p>
    </label>

    <!-- Grid row for Location & City -->
    <div class="form-row">
      <label class="form-group">
        <span class="label-text">{{ t('eventForm.location') }}</span>
        <input
          v-model="formData.location"
          type="text"
          maxlength="120"
          :class="{ 'has-error': errors.location }"
          :placeholder="t('eventForm.locationPlaceholder')"
        />
        <p v-if="errors.location" class="field-error">{{ errors.location }}</p>
      </label>

      <label class="form-group">
        <span class="label-text">{{ t('eventForm.city') }}</span>
        <input
          v-model="formData.city"
          type="text"
          maxlength="100"
          :class="{ 'has-error': errors.city }"
          :placeholder="t('eventForm.cityPlaceholder')"
        />
        <p v-if="errors.city" class="field-error">{{ errors.city }}</p>
      </label>
    </div>

    <!-- Custom Image Upload Dropzone -->
    <div class="form-group">
      <span class="label-text">{{ t('eventForm.coverImage') }}</span>

      <input
        ref="imageInput"
        type="file"
        accept="image/*"
        class="hidden-file-input"
        @change="handleImageUpload"
      />

      <!-- Image Preview State -->
      <div v-if="formData.image" class="image-preview">
        <img :src="formData.image" :alt="formData.name || t('eventForm.previewAlt')" />
        <div class="image-overlay">
          <button type="button" class="btn-subtle" @click="triggerFileInput">
            {{ t('eventForm.changeImage') }}
          </button>
          <button type="button" class="btn-subtle danger" @click="removeImage">
            {{ t('eventForm.removeImage') }}
          </button>
        </div>
      </div>

      <!-- Upload Dropzone Placeholder State -->
      <div v-else :class="{ 'has-error': imageError }" class="upload-dropzone" @click="triggerFileInput">
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
        <span class="upload-title">{{ t('eventForm.uploadTitle') }}</span>
        <span class="upload-hint">{{ t('eventForm.uploadHint') }}</span>
      </div>

      <p v-if="imageError" class="field-error">{{ imageError }}</p>
    </div>

    <!-- Description -->
    <label class="form-group">
      <span class="label-text">{{ t('eventForm.description') }}</span>
      <textarea
        v-model="formData.description"
        maxlength="1200"
        rows="5"
        :class="{ 'has-error': errors.description }"
        :placeholder="t('eventForm.descriptionPlaceholder')"
      />
      <p v-if="errors.description" class="field-error">{{ errors.description }}</p>
    </label>

    <!-- Visibility -->
    <label class="form-group">
      <span class="label-text">{{ t('eventForm.visibility') }}</span>
      <select v-model="formData.public" class="visibility-select">
        <option :value="true">{{ t('eventForm.public') }}</option>
        <option :value="false">{{ t('eventForm.inviteOnly') }}</option>
      </select>
    </label>

    <!-- Invitations -->
    <div class="invite-editor" :class="{ 'is-disabled': formData.public }">
      <span class="label-text">
        {{ t('eventForm.inviteUsers') }}
        <span v-if="formData.public" class="disabled-hint">({{ t('eventForm.inviteOnlyHint') }})</span>
      </span>

      <div class="invite-input-row">
        <input
          v-model="inviteEmail"
          type="email"
          maxlength="120"
          :placeholder="t('eventForm.emailPlaceholder')"
          :disabled="formData.public"
          @keyup.enter.prevent="addInviteEmail"
        />

        <button
          type="button"
          class="btn-add"
          :disabled="formData.public"
          @click="addInviteEmail"
        >
          {{ t('eventForm.add') }}
        </button>
      </div>

      <p v-if="inviteError && !formData.public" class="field-error">{{ inviteError }}</p>

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
            :title="t('eventForm.removeEmail')"
            :disabled="formData.public"
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
.edit-form input.has-error,
.edit-form textarea.has-error,
.edit-form select.has-error,
.upload-dropzone.has-error {
  border-color: #f43f5e;
}

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

/* COMBOBOX DROPDOWN STYLES */
.combobox-group {
  position: relative;
}

.combobox-wrapper {
  position: relative;
}

.combobox-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  margin-top: 0.25rem;
  background: #1e1e24;
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 0.6rem;
  max-height: 180px;
  overflow-y: auto;
  z-index: 50;
  list-style: none;
  padding: 0.4rem 0;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
}

.combobox-dropdown li {
  padding: 0.6rem 0.8rem;
  font-size: 0.9rem;
  color: var(--color-text);
  cursor: pointer;
  transition: background 0.15s ease;
}

.combobox-dropdown li:hover,
.combobox-dropdown li.is-selected {
  background: rgba(20, 184, 166, 0.2);
  color: #fff;
}

.combobox-dropdown li.no-results {
  color: var(--color-text-muted, #9ca3af);
  cursor: default;
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
.edit-form input[type='datetime-local'],
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
  width: 100%;
  box-sizing: border-box;
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
  transition: opacity 0.15s ease;
}

.invite-editor.is-disabled {
  opacity: 0.5;
}

.disabled-hint {
  font-size: 0.75rem;
  font-weight: 400;
  color: var(--color-text-muted, #9ca3af);
  margin-left: 0.4rem;
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

.btn-add:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.15);
}

.btn-add:disabled,
.invite-input-row input:disabled {
  cursor: not-allowed;
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

.tag-remove:hover:not(:disabled) {
  color: #fda4af;
}

.tag-remove:disabled {
  cursor: not-allowed;
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