<script setup lang="ts">
import type { TimetableIndexItem, TimetableIndexLabels } from '@/types/timetableIndex';
import MrtTimetableIndexCard from './MrtTimetableIndexCard.vue';

defineProps<{
  items: TimetableIndexItem[];
  showIntro: boolean;
  labels: TimetableIndexLabels;
}>();
</script>

<template>
  <div class="mrt-timetable-index">
    <p v-if="showIntro" class="mrt-timetable-index__intro">
      {{ labels.intro }}
    </p>
    <nav :aria-label="labels.navAria">
      <ul class="mrt-timetable-index__list">
        <li
          v-for="(item, index) in items"
          :key="`${item.label}-${index}`"
          class="mrt-timetable-index__item"
          :class="item.modifier ? `mrt-timetable-index__item--${item.modifier}` : undefined"
        >
          <MrtTimetableIndexCard :item="item" />
        </li>
      </ul>
    </nav>
  </div>
</template>

<style scoped>
.mrt-timetable-index {
  margin: 0 0 var(--mrt-spacing-xl, 2rem);
  max-width: 42rem;
}

.mrt-timetable-index__intro {
  margin: 0 0 var(--mrt-spacing-lg, 1.5rem);
  font-size: var(--mrt-font-base, 1rem);
  line-height: 1.5;
  color: var(--mrt-color-neutral-700, #3d3d3d);
}

.mrt-timetable-index__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 0.85rem;
}

.mrt-timetable-index__item {
  margin: 0;
}

.mrt-timetable-index__item--green :deep(.mrt-timetable-index__swatch) {
  background: var(--mrt-color-traffic-green);
}

.mrt-timetable-index__item--yellow :deep(.mrt-timetable-index__swatch) {
  background: var(--mrt-color-traffic-yellow);
}

.mrt-timetable-index__item--red :deep(.mrt-timetable-index__swatch) {
  background: var(--mrt-color-traffic-red);
}

.mrt-timetable-index__item--orange :deep(.mrt-timetable-index__swatch) {
  background: var(--mrt-color-traffic-orange);
}
</style>

<style>
.mrt-timetable-index-secondary__title {
  max-width: 42rem;
  margin: 2.25rem auto 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--mrt-color-neutral-200, #b4b4b4);
  font-family: var(--mrt-font-heading, 'Open Sans', sans-serif);
  font-size: clamp(1.25rem, 3vw, 1.5rem);
  font-weight: 700;
  color: var(--mrt-color-green-700, #245610);
}
</style>
