<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import MrtCalendarGrid from '../components/ui/MrtCalendarGrid.vue';
import MrtCalendarNav from '../components/ui/MrtCalendarNav.vue';
import MrtHeading from '../components/ui/MrtHeading.vue';
import MrtHtmlPanel from '../components/ui/MrtHtmlPanel.vue';
import MrtLegend from '../components/ui/MrtLegend.vue';
import MrtMonthDayCell from '../components/ui/MrtMonthDayCell.vue';
import type { MonthVueConfig } from '../config/types';
import type { MonthGridCell } from '../utils/monthGrid';
import { buildMonthGrid } from '../utils/monthGrid';
import { chunkWeekRows } from '../utils/calendarGrid';
import MrtTimetableOverviewView from '../components/overview/MrtTimetableOverviewView.vue';
import { useTimetableOverview } from '../composables/useTimetableOverview';
import { timetableTypeDotClass } from '../shared/calendarDay';
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
const panelRef = ref<InstanceType<typeof MrtHtmlPanel> | null>(null);

const { overview: dayOverview, loading: dayLoading, error: dayError, fetchDayOverview } =
  useTimetableOverview(props.config);

const weekdayHeaders = computed(() => props.config.weekdayHeaders || []);

const typeLabelMap = computed(() => {
  const map: Record<string, string> = {};
  for (const item of props.config.legendTimetableTypes || []) {
    map[item.type] = item.label;
  }
  return map;
});

const legendItems = computed(() => {
  const types = props.config.legendTimetableTypes || [];
  if (types.length > 0) {
    return types.map((item) => ({
      dotClass: timetableTypeDotClass(item.type),
      label: item.label,
    }));
  }
  return [
    {
      dotClass: 'mrt-dot--green',
      label: props.config.legendServiceDay || '',
    },
  ];
});

const legendHints = computed(() => {
  const hints: string[] = [];
  if (showCounts.value && props.config.legendCountHint) {
    hints.push(`(${props.config.legendCountHint})`);
  }
  if (props.config.legendClickHint) {
    hints.push(`(${props.config.legendClickHint})`);
  }
  return hints;
});

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
  await fetchDayOverview(ymd, trainType.value);
}

watch([panelVisible, dayOverview, dayLoading], async ([visible, overview, loading]) => {
  if (!visible || loading || !overview) {
    return;
  }
  await nextTick();
  const el = panelRef.value?.$el as HTMLElement | undefined;
  el?.focus();
});
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
      <template #cell="{ cell }">
        <template v-if="cell.kind === 'empty'" />
        <MrtMonthDayCell
          v-else
          :day="cell.day"
          :info="cell.info"
          :show-counts="showCounts"
          :count-title="config.dayServiceCountTitle"
          :running-aria="config.dayRunningAria"
          :type-labels="typeLabelMap"
          :selected="selectedYmd === cell.info.ymd"
          @click="onDayClick"
        />
      </template>
    </MrtCalendarGrid>

    <div v-if="showLegend" class="mrt-mt-sm">
      <MrtLegend :items="legendItems" :hints="legendHints" />
    </div>

    <MrtHtmlPanel
      ref="panelRef"
      :visible="panelVisible"
      surface
      box
      :loading="dayLoading"
      :error="dayError"
      :loading-text="resolveMrtString(config, 'loading', 'Laddar...')"
    >
      <MrtTimetableOverviewView v-if="dayOverview" :data="dayOverview" />
    </MrtHtmlPanel>
  </div>
</template>
