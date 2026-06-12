<script setup lang="ts">
import { computed } from 'vue';
import MrtDot from './MrtDot.vue';
import type { MrtDotColor, MrtLegendItem } from './types';
import { mrtDotColorFromClass } from './uiContext';

const props = defineProps<{
  items: MrtLegendItem[];
  hints?: string[];
}>();

const resolvedItems = computed(() =>
  props.items.map((item) => ({
    item,
    dotColor: item.dotClass
      ? (mrtDotColorFromClass(item.dotClass) as MrtDotColor | null)
      : null,
  })),
);
</script>

<template>
  <div class="mrt-legend">
    <ul class="mrt-legend-list">
      <li v-for="({ item, dotColor }, i) in resolvedItems" :key="i" class="mrt-legend-list__item">
        <span
          v-if="item.swatchClass"
          class="mrt-legend-list__swatch"
          :class="item.swatchClass"
          aria-hidden="true"
        />
        <MrtDot v-else-if="dotColor" :color="dotColor" />
        <span
          v-else-if="item.dotClass"
          class="mrt-dot"
          :class="item.dotClass"
          aria-hidden="true"
        />
        {{ item.label }}
      </li>
    </ul>
    <p v-for="(hint, i) in hints" :key="`hint-${i}`" class="mrt-legend__hint">
      {{ hint }}
    </p>
  </div>
</template>

<style scoped>
.mrt-legend-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem 1.25rem;
  margin: 0;
  padding: 0.75rem 1.25rem 1.15rem;
  list-style: none;
}

.mrt-legend-list__item {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  font-size: 0.95rem;
}

.mrt-legend-list__swatch {
  flex-shrink: 0;
  width: 1.65rem;
  height: 1.65rem;
}

.mrt-legend-swatch--ok {
  background: var(--mrt-cal-traffic-yellow-bg, var(--mrt-wizard-yellow, var(--mrt-color-accent-600)));
  border: 0;
}

.mrt-legend-swatch--traffic {
  background: var(--mrt-cal-traffic-muted-bg, var(--mrt-color-neutral-200, #e0e0e0));
  border: 0;
}

.mrt-legend-swatch--none {
  background: var(--mrt-wizard-surface, #fff);
  border: 2px solid var(--mrt-color-neutral-500, #767676);
}

.mrt-legend__hint {
  margin: 0.25rem 0 0;
  padding: 0 1.25rem;
  font-size: 0.875rem;
  color: var(--mrt-color-neutral-600, #555);
}

.mrt-legend__hint:first-of-type {
  margin-top: 0;
}

@media (max-width: 48rem) {
  .mrt-legend-list {
    padding-inline: 0.65rem;
    padding-bottom: 1.2rem;
  }
}
</style>
