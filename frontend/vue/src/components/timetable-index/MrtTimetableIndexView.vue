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
