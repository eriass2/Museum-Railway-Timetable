<script setup lang="ts">
import { timetableTypeOverviewClass } from '../../shared/calendarDay';
import type { TimetableOverviewPayload } from '../../types/timetableOverview';
import '../../styles/timetable-overview.css';

withDefaults(
  defineProps<{
    data: TimetableOverviewPayload;
    showDayTitle?: boolean;
  }>(),
  { showDayTitle: true },
);
</script>

<template>
  <div
    class="mrt-ov"
    :class="timetableTypeOverviewClass(data.timetableType)"
    role="region"
    :aria-label="data.title"
  >
    <slot name="prepend" />
    <p v-if="data.typeBanner?.label" class="mrt-ov-banner">
      {{ data.typeBanner.label }}
    </p>
    <h2 v-if="showDayTitle && data.scope === 'day'" class="mrt-ov-day-title">{{ data.title }}</h2>
    <template v-for="(group, gi) in data.groups" :key="gi">
      <slot
        name="group"
        :group="group"
        :index="gi"
        :groups="data.groups"
        :icon-urls="data.iconUrls"
      />
      <div v-if="gi < data.groups.length - 1" class="mrt-ov-separator" aria-hidden="true" />
    </template>
    <slot name="footer" :print-key="data.printKey" />
  </div>
</template>
