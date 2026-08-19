<script setup>
import Event from '@/components/Event.vue'

const event2 = {
  name: 'Spring Festival',
  date: '2026-04-20',
  location: 'Szeged, Bodzafa Utca 4',
  image:
    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=80',
  type: 'Festival',
  description: 'A vibrant celebration with live music, food trucks and local creators.',
}

const events = Array.from({ length: 8 }, (_, index) => ({
  id: `profile-event-${index + 1}`,
  ...event2,
}))
</script>

<template>
  <section class="user_events">
    <div class="event_holder">
      <Event v-for="(event, index) in events" :key="index" v-bind="event" />
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
  align-items: start;
}

@media (max-width: 900px) {
  .event_holder {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}
</style>