<script setup lang="ts">
import type { TimetableIndexItem } from '@/types/timetableIndex';

defineProps<{
  item: TimetableIndexItem;
}>();
</script>

<template>
  <a
    v-if="item.url"
    class="mrt-timetable-index__card"
    :href="item.url"
    :aria-label="`${item.label} — ${item.ariaHint}`"
  >
    <span class="mrt-timetable-index__swatch" aria-hidden="true" />
    <span class="mrt-timetable-index__body">
      <span class="mrt-timetable-index__title">{{ item.label }}</span>
      <span v-if="item.meta" class="mrt-timetable-index__meta">{{ item.meta }}</span>
    </span>
    <span class="mrt-timetable-index__chevron" aria-hidden="true" />
  </a>
  <div v-else class="mrt-timetable-index__card mrt-timetable-index__card--static">
    <span class="mrt-timetable-index__swatch" aria-hidden="true" />
    <span class="mrt-timetable-index__body">
      <span class="mrt-timetable-index__title">{{ item.label }}</span>
      <span v-if="item.meta" class="mrt-timetable-index__meta">{{ item.meta }}</span>
    </span>
    <span class="mrt-timetable-index__chevron" aria-hidden="true" />
  </div>
</template>
