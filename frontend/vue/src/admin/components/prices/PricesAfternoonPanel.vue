<script setup lang="ts">
import { computed, toRef } from 'vue';
import type { PricesPayload } from '../../api/adminRest';
import { AdminTableScroll } from '../ui';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import { useAfternoonPriceCompare } from '../../composables/prices/useAfternoonPriceCompare';
import PricesAfternoonCompare from './PricesAfternoonCompare.vue';
import PricesAfternoonThreshold from './PricesAfternoonThreshold.vue';

const props = defineProps<{
  payload: PricesPayload;
  thresholdMinutes: number;
}>();

const emit = defineEmits<{
  'update:thresholdMinutes': [value: number];
}>();

const cfg = adminConfig();
const categoryKeys = computed(() => Object.keys(props.payload.categories));

const {
  compareZone,
  pricingZones,
  priceCfg,
  normalReturn,
  afternoonReturn,
  compareLabels,
  normalCompareLabels,
} = useAfternoonPriceCompare(toRef(props, 'payload'), toRef(props, 'thresholdMinutes'), cfg);
</script>

<template>
  <section class="mrt-admin-prices-afternoon">
    <h2 class="mrt-admin-prices-afternoon__heading">
      {{ adminStr(cfg, 'pricesAfternoonHeading') }}
    </h2>
    <p class="description">{{ adminStr(cfg, 'pricesAfternoonRule') }}</p>

    <PricesAfternoonThreshold
      :threshold-minutes="thresholdMinutes"
      @update:threshold-minutes="emit('update:thresholdMinutes', $event)"
    />

    <AdminTableScroll>
      <table class="widefat striped mrt-admin-prices-schema__table mrt-admin-responsive-table">
        <thead>
          <tr>
            <th>{{ adminStr(cfg, 'pricesSchemaLabelCol') }}</th>
            <th>{{ adminStr(cfg, 'pricesAfternoonAmountCol') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="key in categoryKeys" :key="`afternoon-${key}`">
            <td :data-label="adminStr(cfg, 'pricesSchemaLabelCol')">{{ payload.categories[key] }}</td>
            <td :data-label="adminStr(cfg, 'pricesAfternoonAmountCol')">
              <input
                v-model.number="payload.afternoon_return[key]"
                type="number"
                min="0"
                step="1"
                class="small-text"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </AdminTableScroll>

    <PricesAfternoonCompare
      v-model:compare-zone="compareZone"
      :pricing-zones="pricingZones"
      :price-cfg="priceCfg"
      :normal-compare-labels="normalCompareLabels"
      :compare-labels="compareLabels"
      :normal-return="normalReturn"
      :afternoon-return="afternoonReturn"
    />
  </section>
</template>

<style scoped>
.mrt-admin-prices-afternoon {
  margin: 24px 0;
  padding: 16px 0 8px;
  border-top: 1px solid #dcdcde;
}

.mrt-admin-prices-afternoon__heading {
  margin: 0 0 8px;
  font-size: 14px;
}
</style>
