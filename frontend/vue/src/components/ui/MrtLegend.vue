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
