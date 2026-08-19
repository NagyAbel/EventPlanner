<script setup lang="ts">
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import arrow from '@/assets/up_arrow.svg'
import search_icon from '@/assets/search_options.svg'

const { t } = useI18n()
const isSearchOpen = ref(true)
</script>

<template>
  <div class="search_holder" :class="{ 'is-collapsed': !isSearchOpen }">
    <div v-if="isSearchOpen" class="search-wrap">
      <div class="search-bar" role="search" aria-label="Event search filters">
        <label class="field field--wide">
          <span class="field-label">{{ t('search.location') || 'Location' }}</span>
          <input class="location_search" type="text" :placeholder="t('search.location_hint')" />
        </label>

        <label class="field field--grow">
          <span class="field-label">{{ t('search.event') || 'Event' }}</span>
          <input class="event_search" type="text" :placeholder="t('search.search_hint')" />
        </label>

        <label class="field">
          <span class="field-label">{{ t('search.date') || 'Date' }}</span>
          <input class="date_search" type="date" aria-label="Event date" />
        </label>

        <label class="field">
          <span class="field-label">{{ t('search.type') || 'Type' }}</span>
          <select class="type_search" aria-label="Event type">
            <option value="">All types</option>
            <option value="conference">Conference</option>
            <option value="concert">Concert</option>
            <option value="festival">Festival</option>
            <option value="workshop">Workshop</option>
          </select>
        </label>
      </div>
    </div>

    <div class="toggle-row">
      <button
        class="close_button"
        type="button"
        :aria-label="isSearchOpen ? 'Hide search filters' : 'Show search filters'"
        :title="isSearchOpen ? 'Hide search filters' : 'Show search filters'"
        @click="isSearchOpen = !isSearchOpen"
      >
        <img
          class="arrow"
          :src="arrow"
          :class="{ hidden: !isSearchOpen }"
          alt=""
        />
        <img
          class="search_option"
          :src="search_icon"
          :class="{ hidden: isSearchOpen }"
          alt=""
        />
      </button>
    </div>
  </div>
</template>

<style scoped>
.search_holder {
  position: sticky;
  top: 100px;
  z-index: 1000;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0;
}

.search_holder.is-collapsed {
  margin-top: 0;
}

.toggle-row {
  width: 100%;
  display: flex;
  justify-content: center;
  margin-top: 0;
}

.close_button {
  width: 100px;
  height: 38px;
  border: none;
  border-radius: 0 0 18px 18px;
  background-color: var( --color-primary);
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.14);
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
}

.search_holder.is-collapsed .close_button {
  width: 100px;
  height: 38px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 0 0 18px 18px;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.14);
}

.close_button:hover {
  box-shadow: 0 14px 28px rgba(15, 23, 42, 0.14);
}

.close_button:focus-visible {
  outline: 3px solid rgba(20, 184, 166, 0.35);
  outline-offset: 3px;
}

.close_button {
  position: relative;
}

.arrow,
.search_option {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  object-fit: contain;
  filter: brightness(0) invert(1);
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.arrow {
  width: 14px;
  height: 14px;
}

.search_option {
  width: 50px;
  height: 50px;
}

.arrow.hidden,
.search_option.hidden {
  opacity: 0;
  pointer-events: none;
}

.search-wrap {
  width: 100%;
  background: linear-gradient(180deg, rgba(33, 104, 105, 0.98), rgba(33, 104, 105, 0.94));
  backdrop-filter: blur(10px);
  box-shadow: 0 10px 30px rgba(23, 42, 58, 0.15);
}

.search-bar {
  width: min(900px, 100%);
  margin: 15px auto;
  display: flex;
  align-items: stretch;
  justify-content: center;
  gap: 0.85rem;
  padding: 0.85rem;

  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 1.25rem;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
}

.field {
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-width: 0;
  background: rgba(255, 255, 255, 0.96);
  border: 1px solid rgba(148, 163, 184, 0.25);
  border-radius: 0.9rem;
  padding: 0.55rem 0.8rem;
  transition: all 0.2s ease;
}

.field:focus-within {
  border-color: rgba(9, 188, 138, 0.9);
  box-shadow: 0 0 0 4px rgba(9, 188, 138, 0.18);
}

.field--wide {
  flex: 1.1;
}

.field--grow {
  flex: 1.8;
}

.field-label {
  display: inline-block;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-text-muted);
  margin-bottom: 0.2rem;
}

.location_search,
.event_search,
.date_search,
.type_search {
  width: 100%;
  border: none;
  background: transparent;
  color: #0f172a;
  font: inherit;
  padding: 0;
  outline: none;
}

.location_search,
.event_search {
  min-height: 2.25rem;
  font-size: 0.98rem;
}

.date_search,
.type_search {
  min-height: 2.25rem;
  font-size: 0.96rem;
  cursor: pointer;
}

.date_search {
  color-scheme: light;
}

.type_search {
  appearance: none;
  background-image: linear-gradient(45deg, transparent 50%, #475569 50%), linear-gradient(135deg, #475569 50%, transparent 50%);
  background-position: calc(100% - 16px) calc(50% - 2px), calc(100% - 11px) calc(50% - 2px);
  background-size: 5px 5px, 5px 5px;
  background-repeat: no-repeat;
  padding-right: 1.75rem;
}

@media (max-width: 950px) {
  .search-wrap {
    padding: 0;
  }

  .search-bar {
    box-sizing: border-box;
    width: 100%;
    flex-wrap: wrap;
    gap: 0.7rem;
    justify-content: stretch;
    border-radius: 0;
        margin:0px

  }

  .field,
  .field--wide,
  .field--grow {
    flex: 1 1 calc(50% - 0.7rem);
    min-width: 150px;
  }
}

@media (max-width: 560px) {
  .field,
  .field--wide,
  .field--grow {
    flex: 1 1 100%;
  }

  .search-bar {
    padding: 0.7rem;
  }
}
</style>
