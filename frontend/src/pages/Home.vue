<script setup lang="ts">
import Event from '@/components/Event.vue'
import Search from '@/components/Search.vue'
const event = {
  name: 'Spring Festival',
  date: '2026-04-20',
  location: 'Szeged, Bodzafa Utca 4',
  image: 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=80',
  type: 'Festival',
  description: 'A vibrant celebration with music, food, and local artists.A vibrant celebration with music, food, and local artists.A vibrant celebration with music, food, and local artists.A vibrant celebration with music, food, and local artists.A vibrant celebration with music, food, and local artists.A vibrant celebration with music, food, and local artists.A vibrant celebration with music, food, and local artists.A vibrant celebration with music, food, and local artists.A vibrant celebration with music, food, and local artists.A vibrant celebration with music, food, and local artists.A vibrant celebration with music, food, and local artists.A vibrant celebration with music, food, and local artists.A vibrant celebration with music, food, and local artists.A vibrant celebration with music, food, and local artists.A vibrant celebration with music, food, and local artists.'
}
const event2 = {
  name: 'Spring Festival',
  date: '2026-04-20',
  location: 'Szeged, Bodzafa Utca 4',
  image: 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=80',
  type: 'Festival',
  description: 'A vibrant celebratio',
}

const events = [
  { id: 'home-event-1', ...event2 },
  { id: 'home-event-2', ...event2 },
  { id: 'home-event-3', ...event2 },
  { id: 'home-event-4', ...event2 },
  { id: 'home-event-5', ...event2 },
  { id: 'home-event-6', ...event2 },
  { id: 'home-event-7', ...event },
  { id: 'home-event-8', ...event },
]
</script>

<template>
  <Search></Search>
  <div class="event_holder">
    <Event v-for="item in events" :key="item.id" v-bind="item" />
  </div>
</template>

<style scoped>
.event_holder {
  width: 100%;
  box-sizing: border-box;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
  display: grid;
  grid-template-columns: repeat(2, minmax(120px, 1fr));
  column-gap: 8rem;
  row-gap: 2.5rem;
}

@media screen and (max-width: 900px) {
  .event_holder {
    grid-template-columns: 1fr;
    gap: 1rem;
    padding: 0 1rem;
  }
}

</style>