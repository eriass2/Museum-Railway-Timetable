<script setup lang="ts">
import { timetableTypeOverviewClass } from '../../shared/calendarDay';
import type { TimetableOverviewPayload } from '../../types/timetableOverview';
import MrtOverviewBanner from './MrtOverviewBanner.vue';

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
    <MrtOverviewBanner v-if="data.typeBanner?.label" :label="data.typeBanner.label" />
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

<style scoped>
.mrt-ov {
  --mrt-ov-header-bg: var(--mrt-color-traffic-green);
  --mrt-ov-header-fg: var(--mrt-color-on-green);
  --mrt-ov-highlight: var(--mrt-from-to-bg, var(--mrt-light-blue-bg, #e8f4fc));
  --mrt-ov-highlight-strong: var(--mrt-from-to-col-bg, #d4ebfa);
  --mrt-ov-bus-bg: var(--mrt-bus-bg, #e3f2fd);
  --mrt-ov-bus-label: var(--mrt-from-to-col-bg, #90caf9);
  --mrt-ov-transfer: var(--mrt-transfer-bg, var(--mrt-special-bg, #fff9c4));
  --mrt-ov-transfer-label: var(--mrt-transfer-col-bg, #fff59d);
  --mrt-ov-stripe: var(--mrt-bg-lightest, #f7fbfd);
  width: 100%;
  max-width: 100%;
  font-size: var(--mrt-font-base, 0.95rem);
  line-height: 1.35;
}

.mrt-ov--green {
  --mrt-ov-header-bg: var(--mrt-color-traffic-green);
  --mrt-ov-header-fg: var(--mrt-color-on-green);
}

.mrt-ov--yellow {
  --mrt-ov-header-bg: var(--mrt-color-traffic-yellow);
  --mrt-ov-header-fg: var(--mrt-color-on-accent);
}

.mrt-ov--red {
  --mrt-ov-header-bg: var(--mrt-color-traffic-red);
  --mrt-ov-header-fg: var(--mrt-color-on-green);
}

.mrt-ov--orange {
  --mrt-ov-header-bg: var(--mrt-color-traffic-orange);
  --mrt-ov-header-fg: var(--mrt-color-on-green);
}

.mrt-ov--blue {
  --mrt-ov-header-bg: var(--mrt-blue-primary, #1e5a96);
  --mrt-ov-header-fg: var(--mrt-color-on-dark);
}

.mrt-ov-day-title {
  margin: 0 0 1rem;
  font-size: 1.25rem;
  font-weight: 700;
}

.mrt-ov-separator {
  height: 1px;
  margin: 1.75rem 0;
  background: #ccc;
}

@media (max-width: 40rem) {
  .mrt-ov-day-title {
    font-size: 1.1rem;
  }
}
</style>
