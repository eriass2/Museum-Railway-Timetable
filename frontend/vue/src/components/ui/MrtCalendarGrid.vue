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
