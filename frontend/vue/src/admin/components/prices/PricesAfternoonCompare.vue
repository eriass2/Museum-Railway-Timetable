<script setup lang="ts">
import type { PriceTableLabels } from '../../../shared/priceLabels';
import type { TripPriceData } from '../../../shared/prices';
import type { PriceCfg } from '../../../shared/priceTypes';
import MrtPriceTable from '../../../components/ui/MrtPriceTable.vue';
import { formatPriceZoneLabel } from '../../../shared/priceZoneLabels';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

defineProps<{
  pricingZones: number[];
  priceCfg: PriceCfg;
  normalCompareLabels: PriceTableLabels;
  compareLabels: PriceTableLabels;
  normalReturn: TripPriceData;
  afternoonReturn: TripPriceData;
}>();

const compareZone = defineModel<number>('compareZone', { required: true });

const cfg = adminConfig();
</script>

<template>
  <h3 class="mrt-admin-prices-afternoon__subheading">
    {{ adminStr(cfg, 'pricesAfternoonCompareTitle') }}
  </h3>
  <p class="description">{{ adminStr(cfg, 'pricesAfternoonCompareHint') }}</p>
  <label class="mrt-admin-prices-afternoon__zone">
    {{ adminStr(cfg, 'pricesPreviewZone') }}
    <select v-model.number="compareZone">
      <option v-for="zone in pricingZones" :key="`afternoon-compare-${zone}`" :value="zone">
        {{ formatPriceZoneLabel(zone) }}
      </option>
    </select>
  </label>

  <div class="mrt-admin-prices-afternoon-compare">
    <div class="mrt-admin-prices-afternoon-compare__col">
      <p class="mrt-admin-prices-afternoon-compare__label">
        {{ adminStr(cfg, 'pricesAfternoonCompareNormal') }}
      </p>
      <MrtPriceTable
        :price-cfg="priceCfg"
        :labels="normalCompareLabels"
        :trip-price="normalReturn"
        :show-all-types="false"
      />
    </div>
    <div class="mrt-admin-prices-afternoon-compare__col">
      <p class="mrt-admin-prices-afternoon-compare__label">
        {{ adminStr(cfg, 'pricesAfternoonCompareAfternoon') }}
      </p>
      <MrtPriceTable
        :price-cfg="priceCfg"
        :labels="compareLabels"
        :trip-price="afternoonReturn"
        :show-all-types="false"
      />
    </div>
  </div>
</template>

<style scoped>
.mrt-admin-prices-afternoon__subheading {
  margin: 20px 0 8px;
  font-size: 13px;
}

.mrt-admin-prices-afternoon__zone {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.mrt-admin-prices-afternoon-compare {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
  margin-top: 8px;
}

.mrt-admin-prices-afternoon-compare__label {
  margin: 0 0 4px;
  font-weight: 600;
}

@media (max-width: 782px) {
  .mrt-admin-prices-afternoon-compare {
    grid-template-columns: 1fr;
  }

  .mrt-admin-prices-afternoon__zone {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
