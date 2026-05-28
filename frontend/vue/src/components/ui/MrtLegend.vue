<script setup lang="ts">
import type { MrtLegendItem } from './types';

defineProps<{
  items: MrtLegendItem[];
  hints?: string[];
}>();
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
