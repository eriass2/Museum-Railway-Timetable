<script setup lang="ts">
import { computed, type MaybeRef, unref } from 'vue';
import type { TripType } from '../../wizard/types';
import type { WizardCfg } from '../../wizard/utils/wizardCfgTypes';
import { cfgRecord, cfgStr } from '../../wizard/utils/wizardLabels';
import {
  PRICE_CAT_KEYS,
  PRICE_TYPE_KEYS,
  formatPriceCell,
  priceMatrixForTrip,
  zonesForStationPair,
} from '../../wizard/utils/prices';
import MrtHeading from './MrtHeading.vue';

const props = defineProps<{
  cfg: MaybeRef<WizardCfg>;
  tripType: MaybeRef<TripType>;
  fromId: MaybeRef<number>;
  toId: MaybeRef<number>;
  showZoneCount?: boolean;
}>();

const cfg = computed(() => unref(props.cfg));
const zones = computed(() => zonesForStationPair(unref(props.fromId), unref(props.toId), cfg.value));
const priceData = computed(() => priceMatrixForTrip(unref(props.tripType), cfg.value, zones.value));
const tickets = computed(() => cfgRecord(cfg.value, 'priceTickets'));
const cats = computed(() => cfgRecord(cfg.value, 'priceCategories'));

const titleSuffix = computed(() => {
  if (!props.showZoneCount) {
    return '';
  }
  return `(${cfgStr(cfg, 'priceZoneLabel', '%d zoner').replace('%d', String(zones.value))})`;
});
</script>

<template>
  <div v-if="priceData" class="mrt-price-block mrt-mt-lg">
    <MrtHeading level="h4" size="md">
      {{ cfgStr(cfg, 'priceTitle', 'Priser') }}
      <span v-if="titleSuffix">{{ titleSuffix }}</span>
    </MrtHeading>
    <div class="mrt-price-block__scroll">
      <table class="mrt-table mrt-price-block__table">
        <thead>
          <tr>
            <th scope="col">
              <span class="mrt-sr-only">{{ cfgStr(cfg, 'priceTableTypeColumn', '') }}</span>
            </th>
            <th v-for="ck in PRICE_CAT_KEYS" :key="ck" scope="col">{{ cats[ck] || ck }}</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="tk in PRICE_TYPE_KEYS"
            :key="tk"
            :class="{ 'mrt-price-block__row--active': tk === priceData.activeType }"
          >
            <th scope="row">{{ tickets[tk] || tk }}</th>
            <td v-for="ck in PRICE_CAT_KEYS" :key="ck">
              {{ formatPriceCell(priceData.matrix[tk]?.[ck], cfg) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-if="cfgStr(cfg, 'priceNote', '')" class="mrt-price-block__note mrt-text-secondary mrt-mt-sm">
      {{ cfgStr(cfg, 'priceNote', '') }}
    </p>
  </div>
</template>
