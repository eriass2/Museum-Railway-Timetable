<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import '../styles/month-calendar.css';
import MrtAlert from '../components/ui/MrtAlert.vue';
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
import { useMonthCalendar } from '../composables/useMonthCalendar';
import { timetableTypeDotClass } from '../shared/calendarDay';
import { overviewUiLabels } from '../shared/overviewUiLabels';
import { resolveMrtString } from '../utils/mrtStrings';

const props = defineProps<{ config: MonthVueConfig }>();

const atts = computed(() => props.config.atts || {});
const showCounts = computed(() => Boolean(atts.value.show_counts));
const showLegend = computed(() => Boolean(atts.value.legend));
const showNav = computed(() => Boolean(atts.value.nav));
const trainType = computed(() => String(atts.value.train_type || ''));
const startMonday = computed(() => Boolean(props.config.startMonday));

const {
  daysInMonth,
  weekdayFirst,
  weekdayFirstSunday,
  monthTitle,
  monthAriaLabel,
  tableCaption,
  dates,
  legendTimetableTypes,
  loading: monthLoading,
  error: monthError,
  shiftMonth,
} = useMonthCalendar(props.config);

const weekdayHeaders = computed(() => props.config.weekdayHeaders || []);

const cells = computed(() =>
  buildMonthGrid(
    daysInMonth.value,
    weekdayFirst.value,
    weekdayFirstSunday.value,
    startMonday.value,
    dates.value,
    { minWeekRows: 6 },
  ),
);

const cellRows = computed(() => chunkWeekRows(cells.value));

const selectedYmd = ref('');
const panelVisible = ref(false);
const panelRef = ref<InstanceType<typeof MrtHtmlPanel> | null>(null);

const { overview: dayOverview, loading: dayLoading, error: dayError, fetchDayOverview } =
  useTimetableOverview(props.config);

const typeLabelMap = computed(() => {
  const map: Record<string, string> = {};
  for (const item of legendTimetableTypes.value) {
    map[item.type] = item.label;
  }
  return map;
});

const legendItems = computed(() => {
  const types = legendTimetableTypes.value;
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

const overviewLabels = computed(() => overviewUiLabels(props.config));

const legendHints = computed(() => {
  const hints: string[] = [];
  const countHint = props.config.legendCountHint?.trim();
  const clickHint = props.config.legendClickHint?.trim();
  if (countHint) {
    hints.push(countHint);
  }
  if (clickHint) {
    hints.push(clickHint);
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
    return cell.info.running ? 'mrt-day-cell mrt-day-cell--running' : 'mrt-day-cell mrt-day-cell--inactive';
  }
  return undefined;
}

function closeDayPanel(): void {
  panelVisible.value = false;
  selectedYmd.value = '';
}

async function onMonthShift(delta: number): Promise<void> {
  if (monthLoading.value) {
    return;
  }
  closeDayPanel();
  await shiftMonth(delta);
}

async function onDayClick(ymd: string): Promise<void> {
  if (!ymd || monthLoading.value) {
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
    :aria-label="monthAriaLabel"
    :data-train-type="trainType"
  >
    <MrtCalendarNav
      v-if="showNav"
      mode="buttons"
      :month-title="monthTitle"
      :prev-text="config.stringsPrevMonth || 'Föregående månad'"
      :next-text="config.stringsNextMonth || 'Nästa månad'"
      :prev-aria="config.stringsPrevMonth || 'Föregående månad'"
      :next-aria="config.stringsNextMonth || 'Nästa månad'"
      @prev="onMonthShift(-1)"
      @next="onMonthShift(1)"
    />
    <MrtHeading v-else level="h2" size="lg">
      {{ monthTitle }}
    </MrtHeading>

    <MrtAlert v-if="monthError" variant="error" live="assertive" class="mrt-mt-sm">
      {{ monthError }}
    </MrtAlert>

    <div
      class="mrt-month__grid-wrap"
      :class="{ 'mrt-month__grid--loading': monthLoading }"
      :aria-busy="monthLoading || undefined"
    >
      <MrtCalendarGrid
        variant="month"
        :weekday-headers="weekdayHeaders"
        :rows="cellRows"
        :caption="tableCaption"
        :grid-label="monthAriaLabel"
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
    </div>

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
      <MrtTimetableOverviewView
        v-if="dayOverview"
        :data="dayOverview"
        :labels="overviewLabels"
      />
    </MrtHtmlPanel>
  </div>
</template>
