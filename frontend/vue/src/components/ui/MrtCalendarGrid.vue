<script setup lang="ts" generic="T">
import MrtAsyncState from './MrtAsyncState.vue';
import MrtCalendarGridTable from './MrtCalendarGridTable.vue';

withDefaults(
  defineProps<{
    variant: 'wizard' | 'month';
    weekdayHeaders: string[];
    rows: T[][];
    gridLabel?: string;
    caption?: string;
    loading?: boolean;
    loadingLabel?: string;
    cellClass?: (cell: T) => string | undefined;
  }>(),
  {
    gridLabel: '',
    caption: '',
    loadingLabel: '',
  },
);
</script>

<template>
  <div
    class="mrt-calendar-grid"
    :class="`mrt-calendar-grid--${variant}`"
    role="region"
    :aria-busy="loading === true ? true : undefined"
  >
    <MrtAsyncState
      v-if="loading !== undefined"
      :loading="loading"
      :loading-text="loadingLabel"
    >
      <MrtCalendarGridTable
        :variant="variant"
        :weekday-headers="weekdayHeaders"
        :rows="rows"
        :grid-label="gridLabel"
        :caption="caption"
        :cell-class="cellClass"
      >
        <template #cell="slotProps">
          <slot name="cell" v-bind="slotProps" />
        </template>
      </MrtCalendarGridTable>
    </MrtAsyncState>
    <MrtCalendarGridTable
      v-else
      :variant="variant"
      :weekday-headers="weekdayHeaders"
      :rows="rows"
      :grid-label="gridLabel"
      :caption="caption"
      :cell-class="cellClass"
    >
      <template #cell="slotProps">
        <slot name="cell" v-bind="slotProps" />
      </template>
    </MrtCalendarGridTable>
  </div>
</template>

<style scoped>
.mrt-calendar-grid--wizard {
  padding: 0.5rem 1.25rem 1rem;
  color: var(--mrt-wizard-text);
  max-width: 100%;
  overflow-x: hidden;
  box-sizing: border-box;
}

@media (max-width: 48rem) {
  .mrt-calendar-grid--wizard {
    padding-inline: 0.65rem;
  }

  .mrt-calendar-grid--wizard :deep(.mrt-calendar-grid__table--wizard th) {
    font-size: 0.85rem;
    padding: 0.25rem 0;
  }

  .mrt-calendar-grid--wizard :deep(.mrt-calendar-day) {
    max-width: 100%;
    min-width: 0;
    min-height: 2.35rem;
    font-size: 1rem;
  }
}
</style>
