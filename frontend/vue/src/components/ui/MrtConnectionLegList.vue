<script setup lang="ts">
import type { ConnectionLegSummaryItem } from '../../wizard/utils/connectionLegSummary';

defineProps<{
  items: ConnectionLegSummaryItem[];
}>();
</script>

<template>
  <ol v-if="items.length" class="mrt-connection-leg-list">
    <template v-for="(item, index) in items" :key="index">
      <li v-if="item.type === 'transfer'" class="mrt-connection-leg-list__transfer">
        {{ item.label }}
      </li>
      <li v-else class="mrt-connection-leg-list__leg">
        <img
          v-if="item.leg.iconUrl"
          :src="item.leg.iconUrl"
          class="mrt-connection-leg-list__icon mrt-train-type-icon-img"
          width="48"
          height="24"
          decoding="async"
          alt=""
        >
        <span v-else class="mrt-connection-leg-list__icon-fallback" aria-hidden="true" />
        <div class="mrt-connection-leg-list__body">
          <p class="mrt-connection-leg-list__vehicle">{{ item.leg.vehicleLabel }}</p>
          <p v-if="item.leg.timeRange" class="mrt-connection-leg-list__time">
            {{ item.leg.timeRange }}
          </p>
          <p v-if="item.leg.route" class="mrt-connection-leg-list__route">
            {{ item.leg.route }}
          </p>
        </div>
      </li>
    </template>
  </ol>
</template>
