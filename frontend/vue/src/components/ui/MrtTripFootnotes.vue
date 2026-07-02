<script setup lang="ts">
import MrtInfoMark from './MrtInfoMark.vue';
import type { TripFootnoteEntry } from '../../shared/stopTimeFootnotes';

defineProps<{
  entries: TripFootnoteEntry[];
}>();
</script>

<template>
  <dl v-if="entries.length" class="mrt-trip-footnotes">
    <div
      v-for="entry in entries"
      :key="entry.text"
      class="mrt-trip-footnotes__row"
    >
      <dt class="mrt-trip-footnotes__mark">
        <MrtInfoMark size="md" />
      </dt>
      <dd class="mrt-trip-footnotes__text">{{ entry.text }}</dd>
    </div>
  </dl>
</template>

<style scoped>
.mrt-trip-footnotes {
  margin: 0.75rem 0 0;
  padding: 0.65rem 0 0;
  border-top: 1px solid var(--mrt-wizard-border, #ddd);
  color: #333333;
  font-size: 0.92rem;
  font-weight: 600;
  line-height: 1.45;
}

.mrt-trip-footnotes__row {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  gap: 0.35rem 0.5rem;
  margin: 0;
}

.mrt-trip-footnotes__row + .mrt-trip-footnotes__row {
  margin-top: 0.35rem;
}

.mrt-trip-footnotes__mark {
  margin: 0;
  display: flex;
  align-items: flex-start;
  padding-top: 0.1rem;
}

.mrt-trip-footnotes__text {
  margin: 0;
  font-weight: 600;
}
</style>
