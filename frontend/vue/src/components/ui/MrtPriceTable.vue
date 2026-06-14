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
import MrtPriceTableList from './MrtPriceTableList.vue';
import MrtPriceTableMatrix from './MrtPriceTableMatrix.vue';
import MrtStack from './MrtStack.vue';

const props = withDefaults(
  defineProps<{
    priceCfg: MaybeRef<PriceCfg>;
    labels: MaybeRef<PriceTableLabels>;
    tripPrice: MaybeRef<TripPriceData | null>;
    dayPrice?: MaybeRef<DayTicketData | null>;
    loading?: boolean;
    /** Summary step: one row for the chosen trip type. Set true to show full matrix. */
    showAllTypes?: boolean;
    /** Summary step layout tweaks for price blocks and lists. */
    context?: 'default' | 'summary';
  }>(),
  { showAllTypes: false, loading: false, context: 'default' },
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
  <MrtStack
    v-if="loading"
    margin-top="lg"
    role="status"
    :class="{ 'mrt-price-table--summary': context === 'summary' }"
  >
    <div class="mrt-price-block">
      {{ labels.title }}…
    </div>
  </MrtStack>
  <MrtStack
    v-else-if="priceData"
    margin-top="lg"
    gap="md"
    :class="{ 'mrt-price-table--summary': context === 'summary' }"
  >
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
          <MrtPriceTableList
            :variant="context === 'summary' ? 'summary' : 'default'"
            :category-keys="categoryKeys"
            :categories="labels.categories"
            :value-for-category="(ck) => priceForCategory(ck, listTicketType)"
          />
        </section>
        <section class="mrt-price-column">
          <MrtHeading level="h5" size="sm" class="mrt-price-column__title">
            {{ dayTicketTitle }}
          </MrtHeading>
          <MrtPriceTableList
            :variant="context === 'summary' ? 'summary' : 'default'"
            :category-keys="categoryKeys"
            :categories="labels.categories"
            :value-for-category="dayPriceForCategory"
          />
        </section>
      </div>

      <template v-else>
        <MrtPriceTableList
          v-if="useListLayout"
          :variant="context === 'summary' ? 'summary' : 'default'"
          :category-keys="categoryKeys"
          :categories="labels.categories"
          :value-for-category="(ck) => priceForCategory(ck, listTicketType)"
        />

        <MrtPriceTableMatrix
          v-else
          :category-keys="categoryKeys"
          :visible-types="visibleTypes"
          :active-type="priceData.activeType"
          :categories="labels.categories"
          :tickets="labels.tickets"
          :type-column-sr="labels.typeColumnSr"
          :title="labels.title"
          :price-for-category="priceForCategory"
        />

        <MrtStack v-if="dayPrices" margin-top="md">
          <div class="mrt-price-block">
            <MrtHeading level="h4" size="md" class="mrt-price-block__title">
              {{ dayTicketTitle }}
            </MrtHeading>
            <MrtPriceTableList
              :variant="context === 'summary' ? 'summary' : 'default'"
              :category-keys="categoryKeys"
              :categories="labels.categories"
              :value-for-category="dayPriceForCategory"
            />
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

.mrt-price-block__note {
  margin: 0.75rem 0 0;
  font-size: 0.9rem;
  line-height: 1.45;
  color: var(--mrt-color-neutral-600, #555);
}

.mrt-price-table--summary .mrt-price-block {
  margin-top: 1.25rem;
  padding-top: 1.25rem;
  border-top: 1px solid var(--mrt-color-neutral-300, #ccc);
}

.mrt-price-table--summary .mrt-price-block + .mrt-price-block {
  margin-top: 1rem;
  padding-top: 0;
  border-top: 0;
}

.mrt-price-table--summary .mrt-price-block__note {
  color: var(--mrt-color-neutral-700, #444);
}

@media (max-width: 48rem) {
  .mrt-price-table--summary .mrt-price-block__title {
    font-size: 1.05rem;
  }
}
</style>
