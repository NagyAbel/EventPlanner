<script setup lang="ts">
import { RouterView } from 'vue-router'
import Header from '@/components/Header.vue'
</script>
<template>
  <Header></Header>
  <RouterView />
</template>

<style>
html,
body,
#app {
  margin: 0;
  padding: 0;
  width: 100%;
}

html,
body {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

html::-webkit-scrollbar,
body::-webkit-scrollbar {
  display: none;
}
</style>