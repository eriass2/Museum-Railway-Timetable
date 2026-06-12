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

<style scoped>
.mrt-vehicle-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem 0.65rem;
  justify-content: flex-end;
}

.mrt-vehicle-row--compact {
  flex-wrap: nowrap;
  gap: 0.35rem;
}

.mrt-vehicle-row__item {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.95rem;
  font-weight: 700;
  min-width: 0;
}

.mrt-vehicle-row__icon {
  display: block;
  width: 48px;
  height: 24px;
}

.mrt-vehicle-row__mark {
  display: inline-block;
  width: 2rem;
  height: 0.65rem;
  background: var(--mrt-color-neutral-400, #999);
}
</style>
