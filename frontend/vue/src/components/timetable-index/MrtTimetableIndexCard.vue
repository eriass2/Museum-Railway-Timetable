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

<style scoped>
.mrt-timetable-index__card {
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 0.85rem 1rem;
  padding: 1rem 1.1rem;
  border: 1px solid var(--mrt-border-default, #d4d4d4);
  border-radius: 0.35rem;
  background: var(--mrt-bg-lightest, #fafafa);
  color: inherit;
  text-decoration: none;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
  transition:
    border-color 0.15s ease,
    box-shadow 0.15s ease,
    transform 0.15s ease;
}

.mrt-timetable-index__card:hover,
.mrt-timetable-index__card:focus-visible {
  border-color: var(--mrt-color-traffic-green);
  box-shadow: 0 4px 14px rgba(41, 99, 16, 0.12);
  transform: translateY(-1px);
  outline: none;
}

.mrt-timetable-index__card--static {
  cursor: default;
}

.mrt-timetable-index__swatch {
  width: 0.45rem;
  align-self: stretch;
  min-height: 2.75rem;
  border-radius: 999px;
  background: var(--mrt-color-traffic-green);
}

.mrt-timetable-index__body {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  min-width: 0;
}

.mrt-timetable-index__title {
  font-size: var(--mrt-font-lg, 1.125rem);
  font-weight: 700;
  line-height: 1.25;
  color: var(--mrt-color-green-800);
}

.mrt-timetable-index__card:hover .mrt-timetable-index__title,
.mrt-timetable-index__card:focus-visible .mrt-timetable-index__title {
  color: var(--mrt-color-green-700);
}

.mrt-timetable-index__meta {
  display: block;
  font-size: var(--mrt-font-sm, 0.875rem);
  line-height: 1.4;
  color: var(--mrt-color-neutral-600);
}

.mrt-timetable-index__chevron {
  width: 0.55rem;
  height: 0.55rem;
  border-right: 2px solid var(--mrt-color-green-700);
  border-bottom: 2px solid var(--mrt-color-green-700);
  transform: rotate(-45deg);
  margin-right: 0.15rem;
}

.mrt-timetable-index__card--static .mrt-timetable-index__chevron {
  visibility: hidden;
}

@media (min-width: 40rem) {
  .mrt-timetable-index__card {
    padding: 1.1rem 1.25rem;
  }
}
</style>
