<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import Event from '@/components/Event.vue'
import Pagination from '@/components/Pagination.vue'
import { useEventStore } from '@/stores/event'

const props = withDefaults(
  defineProps<{
    tab?: 'my-events' | 'joined-events' | 'invited-events'
  }>(),
  {
    tab: 'my-events',
  }
)

const eventStore = useEventStore()

const currentPage = ref(1)
const totalPages = ref(1)

// Dynamic array selector based on active tab
const displayedEvents = computed(() => {
  return eventStore.events
})

// Dynamic empty state message
const emptyStateMessage = computed(() => {
  switch (props.tab) {
    case 'joined-events':
      return "You haven't joined any events yet."
    case 'invited-events':
      return "You haven't been invited to any events yet."
    case 'my-events':
    default:
      return "You haven't created any events yet."
  }
})

async function loadPage(page: number) {
  if (page < 1 || (totalPages.value && page > totalPages.value)) return

  currentPage.value = page

  try {
    // Select the appropriate fetch action based on the tab prop
    let fetchAction: (page?: number) => Promise<any>

    if (props.tab === 'joined-events') {
      fetchAction = eventStore.fetchJoinedEvents
    } else if (props.tab === 'invited-events') {
      fetchAction = eventStore.fetchInvitedEvents
    } else {
      fetchAction = eventStore.fetchUserEvents
    }

    const response = await fetchAction(page)
    totalPages.value = response.last_page || 1

    window.scrollTo({ top: 0, behavior: 'smooth' })
  } catch (err) {
    console.error('Failed to load events for tab', props.tab, err)
  }
}

// Reset to page 1 and fetch fresh list on tab switch
watch(
  () => props.tab,
  () => {
    loadPage(1)
  }
)

onMounted(() => {
  loadPage(1)
})
</script>

<template>
  <section class="profile-events-page">
    <div class="content-wrapper">
      <Transition name="fade-switch" mode="out-in">
        <!-- LOADING STATE -->
        <div v-if="eventStore.loading" key="loading" class="state-container">
          <p class="state-text">Loading events...</p>
        </div>

        <!-- EMPTY STATE -->
        <div
          v-else-if="!displayedEvents.length"
          key="empty"
          class="state-container"
        >
          <p class="state-text">{{ emptyStateMessage }}</p>
        </div>

        <!-- EVENTS GRID -->
        <div v-else key="events" class="event-holder">
          <Event
            v-for="event in displayedEvents"
            :key="event.id"
            v-bind="event"
          />
        </div>
      </Transition>
    </div>

    <!-- PAGINATION CONTROLS -->
    <Pagination
      v-if="!eventStore.loading && totalPages > 1"
      :current-page="currentPage"
      :total-pages="totalPages"
      @page-change="loadPage"
    />
  </section>
</template>

<style scoped>
.profile-events-page {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.content-wrapper {
  min-height: 400px;
  position: relative;
}

.event-holder {
  width: 100%;
  box-sizing: border-box;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.25rem;
}

.state-container {
  min-height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.state-text {
  color: var(--color-text-muted);
  font-size: 0.95rem;
}

/* --- Vue Out-In Smooth Fade Transition --- */
.fade-switch-enter-active,
.fade-switch-leave-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}

.fade-switch-enter-from {
  opacity: 0;
  transform: translateY(6px);
}

.fade-switch-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

@media (max-width: 900px) {
  .event-holder {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}
</style>