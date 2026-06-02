<script setup lang="ts">
import MrtDot from './MrtDot.vue';
import type { MrtDotColor, MrtLegendItem } from './types';
import { mrtDotColorFromClass } from './uiContext';

defineProps<{
  items: MrtLegendItem[];
  hints?: string[];
}>();

function legendDotColor(item: MrtLegendItem): MrtDotColor | null {
  if (!item.dotClass) {
    return null;
  }
  const parsed = mrtDotColorFromClass(item.dotClass);
  return parsed as MrtDotColor | null;
}
</script>

<template>
  <div class="mrt-legend">
    <ul class="mrt-legend-list">
      <li v-for="(item, i) in items" :key="i" class="mrt-legend-list__item">
        <span
          v-if="item.swatchClass"
          class="mrt-legend-list__swatch"
          :class="item.swatchClass"
          aria-hidden="true"
        />
        <MrtDot v-else-if="legendDotColor(item)" :color="legendDotColor(item)!" />
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
