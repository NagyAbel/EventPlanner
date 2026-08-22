<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  currentPage: number
  totalPages: number
}>()

const emit = defineEmits<{
  (e: 'page-change', page: number): void
}>()

const visiblePageNumbers = computed(() => {
  const total = props.totalPages
  const current = props.currentPage
  const delta = 1

  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1)
  }

  const range: (number | string)[] = []
  const left = current - delta
  const right = current + delta

  for (let i = 1; i <= total; i++) {
    if (i === 1 || i === total || (i >= left && i <= right)) {
      range.push(i)
    } else if (i === left - 1 || i === right + 1) {
      range.push('...')
    }
  }

  return range.reduce<(number | string)[]>((acc, item) => {
    if (item === '...' && acc[acc.length - 1] === '...') {
      return acc
    }
    acc.push(item)
    return acc
  }, [])
})

function goToPage(page: number) {
  if (page < 1 || page > props.totalPages || page === props.currentPage) return
  emit('page-change', page)
}
</script>

<template>
  <nav
    v-if="totalPages > 1"
    class="pagination-container"
    aria-label="Pagination Navigation"
  >
    <button
      type="button"
      class="page-btn prev-next"
      :disabled="currentPage === 1"
      @click="goToPage(currentPage - 1)"
    >
      &larr; Previous
    </button>

    <div class="page-numbers">
      <template v-for="(page, index) in visiblePageNumbers" :key="index">
        <span v-if="page === '...'" class="page-ellipsis">&hellip;</span>

        <button
          v-else
          type="button"
          class="page-btn number"
          :class="{ active: page === currentPage }"
          @click="goToPage(Number(page))"
        >
          {{ page }}
        </button>
      </template>
    </div>

    <button
      type="button"
      class="page-btn prev-next"
      :disabled="currentPage === totalPages"
      @click="goToPage(currentPage + 1)"
    >
      Next &rarr;
    </button>
  </nav>
</template>

<style scoped>
.pagination-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  margin-top: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.page-numbers {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.page-btn {
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 0.6rem;
  background: rgba(255, 255, 255, 0.03);
  color: var(--color-text);
  font: inherit;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.page-btn.number {
  min-width: 2.25rem;
  height: 2.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 0.5rem;
}

.page-btn.prev-next {
  padding: 0.5rem 0.9rem;
  height: 2.25rem;
}

.page-ellipsis {
  min-width: 2.25rem;
  height: 2.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-muted);
  font-weight: 600;
  user-select: none;
}

.page-btn:hover:not(:disabled) {
  border-color: rgba(20, 184, 166, 0.5);
  background: rgba(20, 184, 166, 0.12);
  color: var(--color-primary);
}

.page-btn.active {
  background: rgba(20, 184, 166, 0.25);
  border-color: rgba(20, 184, 166, 0.6);
  color: var(--color-primary, #14b8a6);
}

.page-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

@media screen and (max-width: 900px) {
  .pagination-container {
    flex-wrap: wrap;
    gap: 0.5rem;
  }
}
</style>