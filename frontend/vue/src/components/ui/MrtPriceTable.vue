<script setup lang="ts">
import { computed, type MaybeRef, unref } from 'vue';
import type { PriceTableLabels } from '../../shared/priceLabels';
import type { PriceTripType } from '../../shared/prices';
import {
  PRICE_CAT_KEYS,
  PRICE_TYPE_KEYS,
  formatPriceCell,
  priceMatrixForTrip,
  zonesForStationPair,
} from '../../shared/prices';
import type { PriceCfg } from '../../shared/priceTypes';
import MrtHeading from './MrtHeading.vue';

const props = withDefaults(
  defineProps<{
    priceCfg: MaybeRef<PriceCfg>;
    labels: MaybeRef<PriceTableLabels>;
    tripType: MaybeRef<PriceTripType>;
    fromId: MaybeRef<number>;
    toId: MaybeRef<number>;
    /** Summary step: one row for the chosen trip type. Set true to show full matrix. */
    showAllTypes?: boolean;
  }>(),
  { showAllTypes: false },
);

const priceCfg = computed(() => unref(props.priceCfg));
const labels = computed(() => unref(props.labels));
const zones = computed(() => zonesForStationPair(unref(props.fromId), unref(props.toId), priceCfg.value));
const priceData = computed(() => priceMatrixForTrip(unref(props.tripType), priceCfg.value, zones.value));

const cellCfg = computed((): PriceCfg => ({
  ...priceCfg.value,
  priceDash: labels.value.dash,
}));

const visibleTypes = computed(() => {
  if (!priceData.value) {
    return [];
  }
  if (props.showAllTypes) {
    return [...PRICE_TYPE_KEYS];
  }
  return PRICE_TYPE_KEYS.filter((tk) => tk === priceData.value!.activeType);
});

const selectedTypeLabel = computed(() => {
  if (!priceData.value || props.showAllTypes) {
    return '';
  }
  const key = priceData.value.activeType;
  return labels.value.tickets[key] || key;
});
</script>

<template>
  <div v-if="priceData" class="mrt-price-block mrt-mt-lg">
    <MrtHeading level="h4" size="md" class="mrt-price-block__title">
      {{ labels.title }}
      <span v-if="selectedTypeLabel" class="mrt-price-block__title-trip">
        — {{ selectedTypeLabel }}
      </span>
      <span v-if="labels.titleSuffix" class="mrt-price-block__title-suffix">
        {{ labels.titleSuffix }}
      </span>
    </MrtHeading>
    <div class="mrt-price-block__table-wrap">
      <table class="mrt-table mrt-price-block__table">
        <thead>
          <tr>
            <th scope="col" class="mrt-price-block__corner">
              <span class="mrt-sr-only">{{ labels.typeColumnSr || labels.title }}</span>
            </th>
            <th v-for="ck in PRICE_CAT_KEYS" :key="ck" scope="col">
              {{ labels.categories[ck] || ck }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="tk in visibleTypes"
            :key="tk"
            :class="{ 'mrt-price-block__row--active': tk === priceData.activeType }"
          >
            <th scope="row">{{ labels.tickets[tk] || tk }}</th>
            <td
              v-for="ck in PRICE_CAT_KEYS"
              :key="ck"
              :data-label="labels.categories[ck] || ck"
            >
              {{ formatPriceCell(priceData.matrix[tk]?.[ck], cellCfg) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-if="labels.note" class="mrt-price-block__note mrt-text-secondary mrt-mt-sm">
      {{ labels.note }}
    </p>
  </div>
</template>
