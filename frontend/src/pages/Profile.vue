<script setup>
import { ref } from 'vue'
import UserSettings from '@/components/UserSettings.vue'
import UserEvents from '@/components/UserEvents.vue'

const activeTab = ref('my-events')
</script>

<template>
  <div class="profile-page">
    <aside class="profile-sidebar">
      <UserSettings />
    </aside>

    <main class="profile-content">
      <div class="content-header">
        <div class="events-tabs" role="tablist" aria-label="Event sections">
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
      </div>
      <UserEvents v-if="activeTab === 'my-events'" />
      <UserEvents v-else />
    </main>
  </div>
</template>

<style scoped>
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
}

.events-tab.active {
  border-color: rgba(20, 184, 166, 0.75);
  background: rgba(20, 184, 166, 0.16);
}

.events-tab:focus-visible {
  outline: 2px solid rgba(20, 184, 166, 0.75);
  outline-offset: 1px;
}

@media (max-width: 900px) {
  .profile-page {
    grid-template-columns: 1fr;
  }

  .events-tabs {
    width: 100%;
  }

  .events-tab {
    flex: 1;
  }
}
</style>