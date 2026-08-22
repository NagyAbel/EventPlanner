<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import Event from '@/components/Event.vue'
import { useEventStore } from '@/stores/event'

const eventStore = useEventStore()

const currentPage = ref(1)
const hasMore = ref(true)
const loadingMore = ref(false)

const loadMoreTrigger = ref<HTMLElement | null>(null)

async function loadEvents() {
  if (loadingMore.value || !hasMore.value) {
    return
  }

  loadingMore.value = true

  try {
    const result = await eventStore.fetchUserEvents(currentPage.value)

    hasMore.value = result.current_page < result.last_page

    if (hasMore.value) {
      currentPage.value++
    }
  } finally {
    loadingMore.value = false
  }
}

let observer: IntersectionObserver | null = null

onMounted(async () => {
  await loadEvents()

  observer = new IntersectionObserver(
    (entries) => {
      if (entries[0]?.isIntersecting) {
        void loadEvents()
      }
    },
    {
      rootMargin: '500px 0px',
    },
  )

  if (loadMoreTrigger.value) {
    observer.observe(loadMoreTrigger.value)
  }
})

onUnmounted(() => {
  observer?.disconnect()
})
</script>

<template>
  <section class="user_events">
    <div class="event_holder">
      <Event
        v-for="event in eventStore.userEvents"
        :key="event.id"
        v-bind="event"
      />
    </div>

    <div ref="loadMoreTrigger" class="load-more-trigger">
      <div v-if="loadingMore" class="loading">
        Loading more events...
      </div>

      <div
        v-else-if="!hasMore && eventStore.userEvents.length"
        class="end-message"
      >
        No more events.
      </div>

      <div
        v-else-if="!eventStore.userEvents.length && !loadingMore"
        class="empty-message"
      >
        You haven't created any events yet.
      </div>
    </div>
  </section>
</template>

<style scoped>
.user_events {
  width: 100%;
}

.event_holder {
  box-sizing: border-box;

  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));

  gap: 1.25rem;

  width: min(100%, 1200px);
  margin: 0 auto;
}

.loading,
.end-message,
.empty-message {
  width: min(100%, 1200px);
  margin: 0 auto;

  box-sizing: border-box;

  text-align: center;
  padding: 2rem 1rem;

  color: var(--color-text-muted);
}

@media (max-width: 900px) {
  .event_holder {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}</style>