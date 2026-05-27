<script setup lang="ts">
import { computed, type MaybeRef, unref } from 'vue';
import type { TripType } from '../types';
import type { WizardCfg } from '../utils/wizardLabels';
import { cfgStr } from '../utils/wizardLabels';
import {
  PRICE_CAT_KEYS,
  PRICE_TYPE_KEYS,
  formatPriceCell,
  priceMatrixForTrip,
  zonesForStationPair,
} from '../utils/prices';

const props = defineProps<{
  cfg: MaybeRef<WizardCfg>;
  tripType: MaybeRef<TripType>;
  fromId: MaybeRef<number>;
  toId: MaybeRef<number>;
  compactTitle?: boolean;
}>();

const cfg = computed(() => unref(props.cfg));
const zones = computed(() => zonesForStationPair(unref(props.fromId), unref(props.toId), cfg.value));
const priceData = computed(() => priceMatrixForTrip(unref(props.tripType), cfg.value, zones.value));
const tickets = computed(() => (cfg.value.priceTickets || {}) as Record<string, string>);
const cats = computed(() => (cfg.value.priceCategories || {}) as Record<string, string>);
</script>

<template>
  <div
    v-if="priceData"
    class="mrt-jw-prices mrt-journey-wizard__prices mrt-mt-lg"
    :class="{ 'mrt-jw-prices--in-card mrt-journey-wizard__prices--card': compactTitle }"
  >
    <h4 class="mrt-heading mrt-heading--md">
      {{ cfgStr(cfg, 'priceTitle', 'Priser') }}
      <span v-if="!compactTitle">({{ cfgStr(cfg, 'priceZoneLabel', '%d zones').replace('%d', String(zones)) }})</span>
    </h4>
    <div class="mrt-jw-prices__scroll mrt-journey-wizard__prices-scroll mrt-overflow-x-auto">
      <table class="mrt-table mrt-jw-prices__table mrt-journey-wizard__price-table">
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
            :class="{ 'mrt-jw-prices__row--active mrt-journey-wizard__price-row--active': tk === priceData.activeType }"
          >
            <th scope="row">{{ tickets[tk] || tk }}</th>
            <td v-for="ck in PRICE_CAT_KEYS" :key="ck">
              {{ formatPriceCell(priceData.matrix[tk]?.[ck], cfg) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-if="cfgStr(cfg, 'priceNote', '')" class="mrt-text-secondary mrt-jw-prices__note mrt-journey-wizard__price-note mrt-mt-sm">
      {{ cfgStr(cfg, 'priceNote', '') }}
    </p>
  </div>
</template>
