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
import MrtStack from './MrtStack.vue';
import MrtVisuallyHidden from './MrtVisuallyHidden.vue';

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

const zoneNote = computed(() => labels.value.note);

const afternoonClockNote = computed(() => {
  if (!priceData.value?.isAfternoonReturn || !priceCfg.value.priceAfternoonNote) {
    return '';
  }
  return priceCfg.value.priceAfternoonNote;
});

const priceSeniorNote = computed(() => labels.value.seniorNote ?? '');

const stationPurchaseNote = computed(() => labels.value.stationPurchaseNote?.trim() ?? '');

const extraFootnotes = computed(() => labels.value.footnotes?.filter((n) => n.trim() !== '') ?? []);

const displayNotes = computed(() => {
  const notes: string[] = [];
  if (zoneNote.value) {
    notes.push(zoneNote.value);
  }
  if (priceSeniorNote.value) {
    notes.push(priceSeniorNote.value);
  }
  if (stationPurchaseNote.value) {
    notes.push(stationPurchaseNote.value);
  }
  notes.push(...extraFootnotes.value);
  if (afternoonClockNote.value) {
    notes.push(afternoonClockNote.value);
  }
  return notes;
});

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
  <MrtStack v-if="loading" margin-top="lg" role="status">
    <div class="mrt-price-block">
      {{ labels.title }}…
    </div>
  </MrtStack>
  <MrtStack v-else-if="priceData" margin-top="lg" gap="md">
    <div class="mrt-price-block">
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
                <MrtVisuallyHidden>{{ labels.typeColumnSr || labels.title }}</MrtVisuallyHidden>
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

      <MrtStack v-if="dayPrices" margin-top="md">
        <div class="mrt-price-block">
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
      </MrtStack>
    </template>

    <p
      v-for="(note, index) in displayNotes"
      :key="`note-${index}`"
      class="mrt-price-block__note mrt-text-secondary"
    >
      {{ note }}
    </p>
    </div>
  </MrtStack>
</template>

<style scoped>
.mrt-price-block {
  color: var(--mrt-wizard-text, #151515);
}

.mrt-price-block__title {
  margin: 0 0 0.75rem;
  color: inherit;
  font-weight: 900;
  line-height: 1.25;
}

.mrt-price-block__title-trip {
  font-weight: 700;
}

.mrt-price-block__title-suffix {
  display: block;
  margin-top: 0.2rem;
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--mrt-color-neutral-600, #555);
}

.mrt-price-columns {
  display: grid;
  gap: 1rem;
  grid-template-columns: 1fr;
}

.mrt-price-columns--split {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

@media (max-width: 48rem) {
  .mrt-price-columns--split {
    grid-template-columns: 1fr;
  }
}

.mrt-price-column__title {
  margin: 0 0 0.5rem;
  color: inherit;
  font-weight: 700;
  line-height: 1.25;
}

.mrt-price-list {
  margin: 0;
  padding: 0.65rem 0.85rem;
  border-radius: 0;
  background: var(--mrt-color-neutral-100, #ececec);
}

.mrt-price-list__row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.4rem 0;
  border-bottom: 1px solid var(--mrt-color-neutral-200, #d8d8d8);
}

.mrt-price-list__row:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}

.mrt-price-list__row:first-child {
  padding-top: 0;
}

.mrt-price-list__label {
  margin: 0;
  font-size: clamp(0.9rem, 2.5vw, 1rem);
  font-weight: 700;
  color: var(--mrt-color-neutral-700, #444);
}

.mrt-price-list__value {
  margin: 0;
  font-size: clamp(0.95rem, 2.5vw, 1.05rem);
  font-weight: 900;
  color: inherit;
  text-align: right;
  white-space: nowrap;
}

.mrt-price-block__table-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.mrt-price-block__table {
  width: 100%;
  min-width: 0;
  border-collapse: collapse;
  table-layout: fixed;
  color: inherit;
}

.mrt-price-block__table :deep(th),
.mrt-price-block__table :deep(td) {
  padding: 0.5rem 0.4rem;
  text-align: left;
  border: 0;
  font-size: clamp(0.85rem, 2.5vw, 1rem);
  line-height: 1.3;
  vertical-align: top;
  overflow-wrap: anywhere;
}

.mrt-price-block__corner {
  width: 26%;
}

.mrt-price-block__table :deep(thead th:not(.mrt-price-block__corner)) {
  font-weight: 700;
}

.mrt-price-block__table :deep(tbody tr) {
  background: var(--mrt-color-neutral-100, #ececec);
}

.mrt-price-block__table :deep(th[scope='row']) {
  font-weight: 900;
}

.mrt-price-block__row--active :deep(th),
.mrt-price-block__row--active :deep(td) {
  font-weight: 900;
  background: color-mix(in srgb, var(--mrt-wizard-yellow) 40%, #fff);
}

.mrt-price-block__note {
  margin: 0.75rem 0 0;
  font-size: 0.9rem;
  line-height: 1.45;
  color: var(--mrt-color-neutral-600, #555);
}

@media (max-width: 32rem) {
  .mrt-price-block__table-wrap {
    overflow-x: visible;
  }

  .mrt-price-block__table :deep(thead) {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }

  .mrt-price-block__table,
  .mrt-price-block__table :deep(tbody),
  .mrt-price-block__table :deep(tr),
  .mrt-price-block__table :deep(th),
  .mrt-price-block__table :deep(td) {
    display: block;
    width: 100%;
  }

  .mrt-price-block__table :deep(tbody tr) {
    margin-bottom: 0.5rem;
    padding: 0.65rem 0.75rem;
    border-radius: 0;
  }

  .mrt-price-block__table :deep(th[scope='row']) {
    margin-bottom: 0.35rem;
    padding-bottom: 0.35rem;
    border-bottom: 1px solid var(--mrt-color-neutral-300, #ccc);
  }

  .mrt-price-block__table :deep(td) {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.35rem 0;
  }

  .mrt-price-block__table :deep(td)::before {
    content: attr(data-label);
    flex: 1 1 55%;
    font-weight: 700;
    color: var(--mrt-color-neutral-700, #444);
  }
}
</style>
