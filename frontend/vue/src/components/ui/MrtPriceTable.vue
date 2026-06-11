<script setup lang="ts">
import { computed, type MaybeRef, unref } from 'vue';
import type { PriceTableLabels } from '../../shared/priceLabels';
import {
  formatPriceCell,
  priceKeysFromMap,
  type DayTicketData,
  type TripPriceData,
} from '../../shared/prices';
import type { PriceCfg } from '../../shared/priceTypes';
import MrtHeading from './MrtHeading.vue';

const props = withDefaults(
  defineProps<{
    priceCfg: MaybeRef<PriceCfg>;
    labels: MaybeRef<PriceTableLabels>;
    tripPrice: MaybeRef<TripPriceData | null>;
    dayPrice?: MaybeRef<DayTicketData | null>;
    loading?: boolean;
    /** Summary step: one row for the chosen trip type. Set true to show full matrix. */
    showAllTypes?: boolean;
  }>(),
  { showAllTypes: false, loading: false },
);

const priceCfg = computed(() => unref(props.priceCfg));
const labels = computed(() => unref(props.labels));
const priceData = computed(() => unref(props.tripPrice));
const dayPrices = computed(() => unref(props.dayPrice) ?? null);

const cellCfg = computed((): PriceCfg => ({
  ...priceCfg.value,
  priceDash: labels.value.dash,
}));

const categoryKeys = computed(() => priceKeysFromMap(labels.value.categories));

const visibleTypes = computed(() => {
  if (!priceData.value) {
    return [];
  }
  const ticketKeys = priceKeysFromMap(labels.value.tickets);
  if (props.showAllTypes) {
    return ticketKeys;
  }
  const active = priceData.value.activeType;
  return ticketKeys.includes(active) ? [active] : [active];
});

const useListLayout = computed(() => !props.showAllTypes && visibleTypes.value.length === 1);

const useSplitPriceLayout = computed(() => useListLayout.value && !!dayPrices.value?.day);

const listTicketType = computed(() => visibleTypes.value[0] ?? priceData.value?.activeType ?? 'single');

const dayTicketTitle = computed(
  () => priceCfg.value.priceDayTitle || labels.value.tickets.day || 'Heldagsbiljett',
);

const selectedTypeLabel = computed(() => {
  if (!priceData.value || props.showAllTypes) {
    return '';
  }
  if (priceData.value.isAfternoonReturn) {
    return priceCfg.value.priceAfternoonReturnLabel || labels.value.tickets.return || 'return';
  }
  const key = priceData.value.activeType;
  return labels.value.tickets[key] || key;
});

const priceNote = computed(() => {
  if (priceData.value?.isAfternoonReturn && priceCfg.value.priceAfternoonNote) {
    return priceCfg.value.priceAfternoonNote;
  }
  return labels.value.note;
});

const priceSeniorNote = computed(() => labels.value.seniorNote ?? '');

function priceForCategory(catKey: string, ticketType: string): string {
  if (!priceData.value) {
    return '';
  }
  return formatPriceCell(priceData.value.matrix[ticketType]?.[catKey], cellCfg.value);
}

function dayPriceForCategory(catKey: string): string {
  if (!dayPrices.value) {
    return '';
  }
  return formatPriceCell(dayPrices.value.day?.[catKey], cellCfg.value);
}
</script>

<template>
  <div v-if="loading" class="mrt-price-block mrt-mt-lg" role="status">
    {{ labels.title }}…
  </div>
  <div v-else-if="priceData" class="mrt-price-block mrt-mt-lg">
    <MrtHeading level="h4" size="md" class="mrt-price-block__title">
      {{ labels.title }}
      <span v-if="selectedTypeLabel && !useSplitPriceLayout" class="mrt-price-block__title-trip">
        — {{ selectedTypeLabel }}
      </span>
      <span v-if="labels.titleSuffix" class="mrt-price-block__title-suffix">
        {{ labels.titleSuffix }}
      </span>
    </MrtHeading>

    <div
      v-if="useSplitPriceLayout"
      class="mrt-price-columns mrt-price-columns--split"
    >
      <section class="mrt-price-column">
        <MrtHeading level="h5" size="sm" class="mrt-price-column__title">
          {{ selectedTypeLabel }}
        </MrtHeading>
        <dl class="mrt-price-list">
          <div v-for="ck in categoryKeys" :key="ck" class="mrt-price-list__row">
            <dt class="mrt-price-list__label">{{ labels.categories[ck] || ck }}</dt>
            <dd class="mrt-price-list__value">{{ priceForCategory(ck, listTicketType) }}</dd>
          </div>
        </dl>
      </section>
      <section class="mrt-price-column">
        <MrtHeading level="h5" size="sm" class="mrt-price-column__title">
          {{ dayTicketTitle }}
        </MrtHeading>
        <dl class="mrt-price-list">
          <div v-for="ck in categoryKeys" :key="`day-${ck}`" class="mrt-price-list__row">
            <dt class="mrt-price-list__label">{{ labels.categories[ck] || ck }}</dt>
            <dd class="mrt-price-list__value">{{ dayPriceForCategory(ck) }}</dd>
          </div>
        </dl>
      </section>
    </div>

    <template v-else>
      <dl v-if="useListLayout" class="mrt-price-list">
        <div v-for="ck in categoryKeys" :key="ck" class="mrt-price-list__row">
          <dt class="mrt-price-list__label">{{ labels.categories[ck] || ck }}</dt>
          <dd class="mrt-price-list__value">{{ priceForCategory(ck, listTicketType) }}</dd>
        </div>
      </dl>

      <div v-else class="mrt-price-block__table-wrap">
        <table class="mrt-table mrt-price-block__table">
          <thead>
            <tr>
              <th scope="col" class="mrt-price-block__corner">
                <span class="mrt-sr-only">{{ labels.typeColumnSr || labels.title }}</span>
              </th>
              <th v-for="ck in categoryKeys" :key="ck" scope="col">
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
                v-for="ck in categoryKeys"
                :key="ck"
                :data-label="labels.categories[ck] || ck"
              >
                {{ priceForCategory(ck, tk) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="dayPrices" class="mrt-price-block mrt-mt-md">
        <MrtHeading level="h4" size="md" class="mrt-price-block__title">
          {{ dayTicketTitle }}
        </MrtHeading>
        <dl class="mrt-price-list">
          <div v-for="ck in categoryKeys" :key="`day-${ck}`" class="mrt-price-list__row">
            <dt class="mrt-price-list__label">{{ labels.categories[ck] || ck }}</dt>
            <dd class="mrt-price-list__value">{{ dayPriceForCategory(ck) }}</dd>
          </div>
        </dl>
      </div>
    </template>

    <p v-if="priceNote" class="mrt-price-block__note mrt-text-secondary mrt-mt-sm">
      {{ priceNote }}
    </p>
    <p v-if="priceSeniorNote" class="mrt-price-block__note mrt-text-secondary mrt-mt-sm">
      {{ priceSeniorNote }}
    </p>
  </div>
</template>
