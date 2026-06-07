<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import MrtPriceTable from '../../components/ui/MrtPriceTable.vue';
import type { PricesPayload } from '../api/adminRest';
import {
  adminPricePreviewCfg,
  adminPriceTableLabels,
  buildAdminPricePreviewDay,
  buildAdminPricePreviewTrip,
  effectivePricingZones,
} from '../utils/adminPricePreview';
import { adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

const props = defineProps<{
  payload: PricesPayload;
}>();

const cfg = adminConfig();
const previewZone = ref(1);
const previewType = ref('single');

const ticketKeys = computed(() => Object.keys(props.payload.ticket_types));
const pricingZones = computed(() => effectivePricingZones(props.payload));

watch(
  pricingZones,
  (zones) => {
    if (!zones.includes(previewZone.value)) {
      previewZone.value = zones[0] ?? 1;
    }
  },
  { immediate: true },
);

watch(ticketKeys, (keys) => {
  if (!keys.includes(previewType.value)) {
    previewType.value = keys[0] ?? 'single';
  }
});

const labels = computed(() =>
  adminPriceTableLabels(cfg, props.payload, previewZone.value, true),
);

const tripPrice = computed(() =>
  buildAdminPricePreviewTrip(props.payload, previewType.value, previewZone.value, false),
);

const dayPrice = computed(() => buildAdminPricePreviewDay(props.payload, previewZone.value));

const priceCfg = computed(() => adminPricePreviewCfg(cfg));
</script>

<template>
  <section class="mrt-admin-prices-preview">
    <h2 class="mrt-admin-prices-preview__heading">{{ adminStr(cfg, 'pricesPreviewTitle') }}</h2>
    <p class="description">{{ adminStr(cfg, 'pricesPreviewHint') }}</p>
    <div class="mrt-admin-prices-preview__controls">
      <label>
        {{ adminStr(cfg, 'pricesPreviewZone') }}
        <select v-model.number="previewZone">
          <option v-for="zone in pricingZones" :key="`preview-zone-${zone}`" :value="zone">
            {{ zone }}
          </option>
        </select>
      </label>
      <label>
        {{ adminStr(cfg, 'pricesPreviewType') }}
        <select v-model="previewType">
          <option v-for="key in ticketKeys" :key="`preview-type-${key}`" :value="key">
            {{ payload.ticket_types[key] }}
          </option>
        </select>
      </label>
    </div>
    <MrtPriceTable
      :price-cfg="priceCfg"
      :labels="labels"
      :trip-price="tripPrice"
      :day-price="dayPrice"
      :show-all-types="false"
    />
  </section>
</template>

<style scoped>
.mrt-admin-prices-preview {
  margin: 20px 0;
  padding-top: 8px;
  border-top: 1px solid #dcdcde;
}

.mrt-admin-prices-preview__heading {
  margin: 0 0 8px;
  font-size: 14px;
}

.mrt-admin-prices-preview__controls {
  display: flex;
  flex-wrap: wrap;
  gap: 12px 20px;
  margin-bottom: 12px;
}

.mrt-admin-prices-preview__controls label {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

@media (max-width: 782px) {
  .mrt-admin-prices-preview__controls {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }

  .mrt-admin-prices-preview__controls label {
    flex-direction: column;
    align-items: flex-start;
  }

  .mrt-admin-prices-preview__controls select {
    width: 100%;
    max-width: none;
  }
}
</style>
