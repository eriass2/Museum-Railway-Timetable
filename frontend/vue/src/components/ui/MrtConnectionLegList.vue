<script setup lang="ts">
import type { ConnectionLegSummaryItem } from '../../shared/connectionLegDisplay';

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

<style scoped>
.mrt-connection-leg-list {
  margin: 0.75rem 0 0;
  padding: 0;
  list-style: none;
}

.mrt-connection-leg-list__leg {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  gap: 0.45rem 0.65rem;
  align-items: start;
  padding: 0.55rem 0;
}

.mrt-connection-leg-list__icon {
  display: block;
  width: 48px;
  height: 24px;
  margin-top: 0.1rem;
  flex-shrink: 0;
}

.mrt-connection-leg-list__icon-fallback {
  display: inline-block;
  width: 2rem;
  height: 0.65rem;
  margin-top: 0.35rem;
  background: var(--mrt-color-neutral-400, #999);
  flex-shrink: 0;
}

.mrt-connection-leg-list__body {
  min-width: 0;
}

.mrt-connection-leg-list__vehicle {
  margin: 0;
  font-size: 0.98rem;
  font-weight: 700;
  line-height: 1.35;
  overflow-wrap: anywhere;
}

.mrt-connection-leg-list__time {
  margin: 0.15rem 0 0;
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.3;
}

.mrt-connection-leg-list__route {
  margin: 0.1rem 0 0;
  font-size: 0.92rem;
  line-height: 1.35;
  color: var(--mrt-color-text-muted, #555);
  overflow-wrap: anywhere;
}

.mrt-connection-leg-list__transfer {
  margin: 0.15rem 0;
  padding: 0.45rem 0.65rem;
  border-radius: 0.35rem;
  background: var(--mrt-transfer-bg, var(--mrt-special-bg, #fff9c4));
  color: #333;
  font-size: 0.92rem;
  font-weight: 700;
  line-height: 1.35;
  text-align: center;
  overflow-wrap: anywhere;
}

@container mrt-summary-card (max-width: 22rem) {
  .mrt-connection-leg-list__leg {
    grid-template-columns: minmax(0, 1fr);
    gap: 0.35rem;
  }

  .mrt-connection-leg-list__icon {
    width: 36px;
    height: 18px;
    margin-top: 0;
  }

  .mrt-connection-leg-list__icon-fallback {
    width: 1.5rem;
    margin-top: 0.15rem;
  }

  .mrt-connection-leg-list__vehicle {
    font-size: 0.95rem;
  }

  .mrt-connection-leg-list__time {
    font-size: 1rem;
  }

  .mrt-connection-leg-list__route {
    font-size: 0.88rem;
  }

  .mrt-connection-leg-list__transfer {
    font-size: 0.88rem;
    padding: 0.4rem 0.55rem;
  }
}
</style>
