<script setup lang="ts">
import type { MrtVehicleItem } from './types';

defineProps<{
  items: MrtVehicleItem[];
  /** Icons only — labels via aria-label and title (trip card summary). */
  compact?: boolean;
}>();
</script>

<template>
  <div class="mrt-vehicle-row" :class="{ 'mrt-vehicle-row--compact': compact }">
    <span
      v-for="(item, i) in items"
      :key="i"
      class="mrt-vehicle-row__item"
      :class="item.kind ? `mrt-vehicle-row__item--${item.kind}` : undefined"
      :aria-label="compact ? item.label : undefined"
      :title="compact ? item.label : undefined"
    >
      <img
        v-if="item.iconUrl"
        :src="item.iconUrl"
        class="mrt-vehicle-row__icon mrt-train-type-icon-img"
        width="48"
        height="24"
        decoding="async"
        alt=""
      >
      <span v-else class="mrt-vehicle-row__mark" aria-hidden="true" />
      <span v-if="!compact" class="mrt-vehicle-row__label">{{ item.label }}</span>
    </span>
  </div>
</template>
