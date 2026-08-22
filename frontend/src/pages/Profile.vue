<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import UserSettings from '@/components/UserSettings.vue'
import ProfileEvents from '@/components/ProfileEvents.vue'

const router = useRouter()

const activeTab = ref('my-events')

function createEvent() {
  router.push('/events/create')
}
</script>

<template>
  <div class="profile-page">
    <aside class="profile-sidebar">
      <UserSettings />
    </aside>

    <main class="profile-content">
      <div class="content-header">
        <div
          class="events-tabs"
          role="tablist"
          aria-label="Event sections"
        >
          <button
            type="button"
            class="events-tab"
            :class="{ active: activeTab === 'my-events' }"
            role="tab"
            :aria-selected="activeTab === 'my-events'"
            @click="activeTab = 'my-events'"
          >
            My Events
          </button>

          <button
            type="button"
            class="events-tab"
            :class="{ active: activeTab === 'joined-events' }"
            role="tab"
            :aria-selected="activeTab === 'joined-events'"
            @click="activeTab = 'joined-events'"
          >
            Joined Events
          </button>
        </div>

        <button
          type="button"
          class="create-button"
          @click="createEvent"
        >
          <span class="create-icon">+</span>
          Create Event
        </button>
      </div>

      <!-- Pass activeTab into UserEvents -->
      <ProfileEvents :tab="activeTab" />
    </main>
  </div>
</template>

<style scoped>
/* Unchanged CSS */
.profile-page {
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  gap: 1.25rem;
  width: min(1400px, 100%);
  margin: 0 auto;
  padding: 1rem;
  box-sizing: border-box;
}

.profile-sidebar {
  min-width: 0;
}

.profile-content {
  min-width: 0;
}

.content-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.8rem;
}

.events-tabs {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.25rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 0.8rem;
  background: rgba(255, 255, 255, 0.03);
}

.events-tab {
  border: 1px solid transparent;
  background: transparent;
  color: var(--color-text);
  border-radius: 0.6rem;
  min-height: 2.2rem;
  padding: 0.4rem 0.85rem;
  font-weight: 700;
  cursor: pointer;
  transition:
    background 0.15s ease,
    border-color 0.15s ease,
    transform 0.15s ease;
}

.events-tab.active {
  border-color: rgba(20, 184, 166, 0.75);
  background: rgba(20, 184, 166, 0.16);
}

.events-tab:hover:not(.active) {
  background: rgba(255, 255, 255, 0.05);
}

.create-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  min-height: 2.35rem;
  padding: 0.45rem 0.9rem;
  border: 1px solid rgba(20, 184, 166, 0.7);
  border-radius: 0.7rem;
  background: rgba(20, 184, 166, 0.18);
  color: var(--color-text);
  font: inherit;
  font-weight: 700;
  cursor: pointer;
  transition:
    background 0.15s ease,
    border-color 0.15s ease,
    transform 0.15s ease,
    box-shadow 0.15s ease;
}

.create-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.15rem;
  height: 1.15rem;
  border-radius: 50%;
  background: rgba(20, 184, 166, 0.25);
  color: var(--color-primary);
  font-size: 1rem;
  line-height: 1;
}

.create-button:hover {
  background: rgba(20, 184, 166, 0.28);
  border-color: rgba(20, 184, 166, 0.95);
  box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.08);
  transform: translateY(-1px);
}

.create-button:active {
  transform: translateY(0);
}

.create-button:focus-visible,
.events-tab:focus-visible {
  outline: 2px solid rgba(20, 184, 166, 0.75);
  outline-offset: 2px;
}

@media (max-width: 900px) {
  .profile-page {
    grid-template-columns: 1fr;
  }

  .content-header {
    align-items: stretch;
  }

  .events-tabs {
    flex: 1;
  }

  .events-tab {
    flex: 1;
  }
}

@media (max-width: 560px) {
  .content-header {
    flex-direction: column;
  }

  .events-tabs {
    width: 100%;
  }

  .create-button {
    width: 100%;
  }
}
</style>