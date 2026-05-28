<script setup lang="ts">
import { computed, ref } from 'vue';
import MrtCalendarGrid from '../components/ui/MrtCalendarGrid.vue';
import MrtCalendarNav from '../components/ui/MrtCalendarNav.vue';
import MrtHeading from '../components/ui/MrtHeading.vue';
import MrtHtmlPanel from '../components/ui/MrtHtmlPanel.vue';
import MrtLegend from '../components/ui/MrtLegend.vue';
import type { MonthVueConfig } from '../config/types';
import type { MonthGridCell } from '../utils/monthGrid';
import { buildMonthGrid } from '../utils/monthGrid';
import { chunkWeekRows } from '../utils/calendarGrid';
import { useTimetableHtml } from '../composables/useTimetableHtml';
import { resolveMrtString } from '../utils/mrtStrings';

const props = defineProps<{ config: MonthVueConfig }>();

const atts = computed(() => props.config.atts || {});
const showCounts = computed(() => Boolean(atts.value.show_counts));
const showLegend = computed(() => Boolean(atts.value.legend));
const showNav = computed(() => Boolean(atts.value.nav));
const trainType = computed(() => String(atts.value.train_type || ''));

const dates = computed(() => props.config.dates || {});
const cells = computed(() =>
  buildMonthGrid(
    Number(props.config.daysInMonth) || 0,
    Number(props.config.weekdayFirst) || 1,
    Number(props.config.weekdayFirstSunday) || 0,
    Boolean(props.config.startMonday),
    dates.value,
  ),
);

const cellRows = computed(() => chunkWeekRows(cells.value));

const selectedYmd = ref('');
const panelVisible = ref(false);

const { html: dayHtml, loading: dayLoading, error: dayError, fetchDayHtml } =
  useTimetableHtml(props.config);

const weekdayHeaders = computed(() => props.config.weekdayHeaders || []);

const legendItems = computed(() => [
  {
    dotClass: 'mrt-dot--green',
    label: props.config.legendServiceDay || '',
  },
]);

type MonthDayCell = Extract<MonthGridCell, { kind: 'day' }>;

function monthDay(cell: MonthGridCell): MonthDayCell | null {
  return cell.kind === 'day' ? cell : null;
}

function monthCellClass(cell: MonthGridCell): string | undefined {
  if (cell.kind === 'empty') {
    return 'mrt-empty';
  }
  if (cell.kind === 'day' && !cell.info.running) {
    return 'mrt-day-cell mrt-day-cell--inactive';
  }
  if (cell.kind === 'day') {
    return 'mrt-day-cell mrt-day-cell--running';
  }
  return undefined;
}

async function onDayClick(ymd: string): Promise<void> {
  if (!ymd) {
    return;
  }
  selectedYmd.value = ymd;
  panelVisible.value = true;
  await fetchDayHtml(ymd, trainType.value);
}
</script>

<template>
  <div
    class="mrt-month mrt-my-1"
    role="region"
    :aria-label="config.monthAriaLabel || ''"
    :data-train-type="trainType"
  >
    <MrtCalendarNav
      v-if="showNav"
      mode="links"
      :month-title="config.monthTitle || ''"
      :prev-href="config.prevMonthUrl || '#'"
      :next-href="config.nextMonthUrl || '#'"
      :prev-text="config.stringsPrevMonth || 'Föregående månad'"
      :next-text="config.stringsNextMonth || 'Nästa månad'"
    />
    <MrtHeading v-else level="h2" size="lg">
      {{ config.monthTitle || '' }}
    </MrtHeading>

    <MrtCalendarGrid
      variant="month"
      :weekday-headers="weekdayHeaders"
      :rows="cellRows"
      :caption="config.tableCaption || ''"
      :grid-label="config.monthAriaLabel || ''"
      :cell-class="monthCellClass"
    >
      <template #cell="{ cell: rawCell }">
        <template v-if="(rawCell as MonthGridCell).kind === 'empty'" />
        <template v-else-if="monthDay(rawCell as MonthGridCell) && !monthDay(rawCell as MonthGridCell)!.info.running">
          <span class="mrt-daynum">{{ monthDay(rawCell as MonthGridCell)!.day }}</span>
        </template>
        <button
          v-else-if="monthDay(rawCell as MonthGridCell)"
          type="button"
          class="mrt-day mrt-running mrt-day-clickable mrt-cursor-pointer"
          :class="{ 'mrt-day-active': selectedYmd === monthDay(rawCell as MonthGridCell)!.info.ymd }"
          :aria-pressed="selectedYmd === monthDay(rawCell as MonthGridCell)!.info.ymd"
          @click="onDayClick(monthDay(rawCell as MonthGridCell)!.info.ymd || '')"
        >
          <span class="mrt-daynum" aria-hidden="true">{{ monthDay(rawCell as MonthGridCell)!.day }}</span>
          <span class="mrt-dot" aria-hidden="true">{{
            showCounts ? monthDay(rawCell as MonthGridCell)!.info.count : '•'
          }}</span>
        </button>
      </template>
    </MrtCalendarGrid>

    <div v-if="showLegend" class="mrt-mt-sm">
      <MrtLegend :items="legendItems" />
      <span v-if="showCounts" class="mrt-text-small mrt-opacity-85 mrt-ml-sm">
        ({{ config.legendCountHint || '' }})
      </span>
      <span class="mrt-text-tertiary mrt-text-small">
        ({{ config.legendClickHint || '' }})
      </span>
    </div>

    <MrtHtmlPanel
      :visible="panelVisible"
      surface
      box
      :loading="dayLoading"
      :error="dayError"
      :loading-text="resolveMrtString(config, 'loading', 'Laddar...')"
    >
      <!-- Trusted server HTML — see frontend/vue/TRUSTED_HTML.md -->
      <div v-html="dayHtml" />
    </MrtHtmlPanel>
  </div>
</template>
