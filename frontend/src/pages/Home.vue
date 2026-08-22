<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Event from '@/components/Event.vue'
import Search from '@/components/Search.vue'
import { useEventStore, type FetchEventsOptions } from '@/stores/event'
import Pagination from '@/components/Pagination.vue'

const eventStore = useEventStore()

const currentPage = ref(1)
const totalPages = ref(1)
const currentFilters = ref<FetchEventsOptions>({})

async function loadPage(page: number) {
  if (page < 1 || (totalPages.value && page > totalPages.value)) return

  currentPage.value = page

  try {
    const response = await eventStore.fetchEvents({
      ...currentFilters.value,
      page,
    })    
    totalPages.value = response.last_page || 1
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } catch (err) {
    console.error('Failed to fetch events for page', page, err)
  }
}

function handleSearch(filters: FetchEventsOptions) {
  currentFilters.value = filters
  loadPage(1)
}

onMounted(() => {
  loadPage(1)
})
</script>

<template>
  <Search @search="handleSearch" />
  <main class="home-page">
    <div class="content-wrapper">
      <!-- FADE TRANSITION FOR STATES -->
      <Transition name="fade-switch" mode="out-in">
        <!-- LOADING STATE -->
        <div v-if="eventStore.loading" key="loading" class="state-container">
          <p class="state-text">Loading events...</p>
        </div>

        <!-- EMPTY STATE -->
        <div v-else-if="!eventStore.events.length" key="empty" class="state-container">
          <p class="state-text">No events found.</p>
        </div>

        <!-- EVENTS GRID WITH SMOOTH FADE IN -->
        <div v-else key="events" class="event-holder">
          <Event
            v-for="event in eventStore.events"
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
  </main>
</template>

<style scoped>
.home-page {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem 3rem;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Minimum height prevents page height jump while loading */
.content-wrapper {
  min-height: 400px;
  position: relative;
}

.event-holder {
  width: 100%;
  box-sizing: border-box;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  column-gap: min(10%, 10em);
  row-gap: 2rem;
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

@media screen and (max-width: 900px) {
  .home-page {
    padding: 0 1rem 2rem;
  }

  .event-holder {
    grid-template-columns: 1fr;
    gap: 1.25rem;
  }
}
</style>