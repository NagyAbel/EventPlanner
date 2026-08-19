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
</script>

<template>
  <Search></Search>
  <div class = "event_holder">
    <Event v-bind="event2" />
    <Event v-bind="event2" />
    <Event v-bind="event2" />
    <Event v-bind="event2" />
    <Event v-bind="event2" />
    <Event v-bind="event2" />
    <Event v-bind="event" />
    <Event v-bind="event" />
  </div>
</template>

<style scoped>
.event_holder {
  width: 100%;
  box-sizing: border-box;
  padding-left: 50px;
  padding-right:50px;
  display:grid;
  grid-template-columns: 1fr 1fr;

}
@media screen and (max-width: 1500px) {
  .event_holder{
      grid-template-columns: 1fr;
      padding: 0px;
      gap: 5px;
  }
}

</style>