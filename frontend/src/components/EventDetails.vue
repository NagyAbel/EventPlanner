<script setup lang="ts">
import type { EventModel } from '@/stores/event'

const props = defineProps<{
  event: EventModel
}>()

const formattedDate = (date: string) => {
  if (!date) return 'No date set'

  return new Intl.DateTimeFormat('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(new Date(date))
}

const formattedTime = (date: string) => {
  if (!date) return ''

  return new Intl.DateTimeFormat('en-US', {
    hour: 'numeric',
    minute: '2-digit',
  }).format(new Date(date))
}

const visibilityLabel = (visibility: EventModel['visibility']) => {
  return visibility === 'invite-only'
    ? 'Invite Only'
    : 'Public'
}
</script>

<template>
  <article class="event-details">
    <!-- Hero image -->
    <div
      v-if="props.event.cover_image"
      class="event-hero"
    >
      <img
        :src="props.event.cover_image"
        :alt="props.event.name"
      />

      <div class="hero-overlay">
        <span class="event-type">
          {{ props.event.type }}
        </span>

        <h2>
          {{ props.event.name }}
        </h2>
      </div>
    </div>

    <!-- No image -->
    <div
      v-else
      class="event-hero event-hero-empty"
    >
      <div>
        <span class="event-type">
          {{ props.event.type }}
        </span>

        <h2>
          {{ props.event.name }}
        </h2>
      </div>
    </div>

    <div class="event-content">
      <!-- Main information -->
      <div class="event-main">
        <div class="event-section">
          <div class="section-heading">
            <span class="section-icon">📅</span>

            <div>
              <p class="section-label">
                Date & Time
              </p>

              <p class="section-value">
                {{ formattedDate(props.event.date) }}
              </p>

              <p class="section-secondary">
                {{ formattedTime(props.event.date) }}
              </p>
            </div>
          </div>
        </div>

        <div class="event-section">
          <div class="section-heading">
            <span class="section-icon">📍</span>

            <div>
              <p class="section-label">
                Location
              </p>

              <p class="section-value">
                {{ props.event.location }}
              </p>

              <p class="section-secondary">
                {{ props.event.city }}
              </p>
            </div>
          </div>
        </div>

        <!-- Description -->
        <section
          v-if="props.event.description"
          class="description-section"
        >
          <p class="section-label">
            About this event
          </p>

          <p class="description">
            {{ props.event.description }}
          </p>
        </section>
      </div>

      <!-- Sidebar -->
      <aside class="event-sidebar">
        <!-- Event status -->
        <div class="info-card">
          <p class="section-label">
            Visibility
          </p>

          <span
            class="visibility-badge"
            :class="{
              'is-private':
                props.event.visibility === 'invite-only',
            }"
          >
            <span class="status-dot" />
            {{ visibilityLabel(props.event.visibility) }}
          </span>
        </div>

        <!-- Owner -->
        <div
          v-if="props.event.owner"
          class="info-card"
        >
          <p class="section-label">
            Organized by
          </p>

          <div class="owner">
            <div class="owner-avatar">
              {{ props.event.owner.name?.charAt(0).toUpperCase() }}
            </div>

            <div>
              <p class="owner-name">
                {{ props.event.owner.name }}
              </p>

              <p class="owner-email">
                {{ props.event.owner.email }}
              </p>
            </div>
          </div>
        </div>

        <!-- Invitations -->
        <div
          v-if="
            props.event.visibility === 'invite-only'
          "
          class="info-card"
        >
          <div class="invite-heading">
            <p class="section-label">
              Invited guests
            </p>

            <span class="guest-count">
              {{ props.event.invitedEmails?.length || 0 }}
            </span>
          </div>

          <ul
            v-if="props.event.invitedEmails?.length"
            class="guest-list"
          >
            <li
              v-for="email in props.event.invitedEmails"
              :key="email"
            >
              <span class="guest-avatar">
                {{ email.charAt(0).toUpperCase() }}
              </span>

              <span class="guest-email">
                {{ email }}
              </span>
            </li>
          </ul>

          <p
            v-else
            class="empty-guests"
          >
            No guests have been invited yet.
          </p>
        </div>
      </aside>
    </div>
  </article>
</template>

<style scoped>
.event-details {
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
  background: rgba(255, 255, 255, 0.025);
}

/* Hero */

.event-hero {
  position: relative;
  width: 100%;
  height: 340px;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.04);
}

.event-hero img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.event-hero::after {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(
      to top,
      rgba(0, 0, 0, 0.8),
      rgba(0, 0, 0, 0.08) 65%
    );
  content: '';
}

.event-hero-empty {
  display: flex;
  align-items: flex-end;
  padding: 2rem;
  box-sizing: border-box;
  background:
    radial-gradient(
      circle at 20% 20%,
      rgba(20, 184, 166, 0.2),
      transparent 45%
    ),
    rgba(255, 255, 255, 0.04);
}

.event-hero-empty::after {
  display: none;
}

.hero-overlay {
  position: absolute;
  z-index: 1;
  right: 0;
  bottom: 0;
  left: 0;
  padding: 2rem;
}

.hero-overlay h2,
.event-hero-empty h2 {
  margin: 0.45rem 0 0;
  color: white;
  font-size: clamp(1.7rem, 4vw, 2.4rem);
  line-height: 1.1;
}

.event-type {
  display: inline-flex;
  padding: 0.3rem 0.6rem;
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.35);
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

/* Content */

.event-content {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 300px;
  gap: 2rem;
  padding: 1.5rem;
}

.event-main {
  min-width: 0;
}

.event-section {
  padding: 1rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}

.event-section:first-child {
  padding-top: 0;
}

.section-heading {
  display: flex;
  align-items: flex-start;
  gap: 0.85rem;
}

.section-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.3rem;
  height: 2.3rem;
  flex-shrink: 0;
  border-radius: 0.65rem;
  background: rgba(20, 184, 166, 0.1);
  font-size: 1rem;
}

.section-label {
  margin: 0 0 0.3rem;
  color: var(--color-text-muted);
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.section-value {
  margin: 0;
  color: var(--color-text);
  font-size: 1rem;
  font-weight: 600;
}

.section-secondary {
  margin: 0.2rem 0 0;
  color: var(--color-text-muted);
  font-size: 0.85rem;
}

/* Description */

.description-section {
  padding-top: 1.4rem;
}

.description {
  margin: 0.6rem 0 0;
  color: var(--color-text);
  font-size: 0.95rem;
  line-height: 1.7;
  white-space: pre-wrap;
}

/* Sidebar */

.event-sidebar {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.info-card {
  padding: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 0.8rem;
  background: rgba(255, 255, 255, 0.025);
}

.visibility-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  margin-top: 0.25rem;
  padding: 0.35rem 0.65rem;
  border-radius: 999px;
  background: rgba(20, 184, 166, 0.1);
  color: var(--color-text);
  font-size: 0.8rem;
  font-weight: 600;
}

.visibility-badge.is-private {
  background: rgba(245, 158, 11, 0.1);
}

.status-dot {
  width: 0.45rem;
  height: 0.45rem;
  border-radius: 50%;
  background: #14b8a6;
}

.is-private .status-dot {
  background: #f59e0b;
}

/* Owner */

.owner {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  margin-top: 0.7rem;
}

.owner-avatar,
.guest-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border-radius: 50%;
  background: rgba(20, 184, 166, 0.14);
  color: var(--color-primary);
  font-weight: 700;
}

.owner-avatar {
  width: 2.4rem;
  height: 2.4rem;
}

.owner-name {
  margin: 0;
  color: var(--color-text);
  font-size: 0.85rem;
  font-weight: 600;
}

.owner-email {
  margin: 0.15rem 0 0;
  color: var(--color-text-muted);
  font-size: 0.72rem;
}

/* Invitations */

.invite-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.guest-count {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 1.5rem;
  height: 1.5rem;
  padding: 0 0.35rem;
  box-sizing: border-box;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.07);
  color: var(--color-text-muted);
  font-size: 0.7rem;
  font-weight: 700;
}

.guest-list {
  display: grid;
  gap: 0.4rem;
  margin: 0.7rem 0 0;
  padding: 0;
  list-style: none;
}

.guest-list li {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  min-width: 0;
}

.guest-avatar {
  width: 1.7rem;
  height: 1.7rem;
  font-size: 0.65rem;
}

.guest-email {
  overflow: hidden;
  color: var(--color-text-muted);
  font-size: 0.75rem;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.empty-guests {
  margin: 0.6rem 0 0;
  color: var(--color-text-muted);
  font-size: 0.78rem;
}

/* Responsive */

@media (max-width: 800px) {
  .event-content {
    grid-template-columns: 1fr;
  }

  .event-sidebar {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 550px) {
  .event-hero {
    height: 260px;
  }

  .hero-overlay,
  .event-hero-empty {
    padding: 1.25rem;
  }

  .event-content {
    padding: 1rem;
  }

  .event-sidebar {
    display: flex;
  }
}
</style>