<script setup lang="ts">
import { computed } from 'vue';
import MrtPublicAppShell from '../components/layout/MrtPublicAppShell.vue';
import MrtAlert from '../components/ui/MrtAlert.vue';
import MrtCalendarGrid from '../components/ui/MrtCalendarGrid.vue';
import MrtCalendarNav from '../components/ui/MrtCalendarNav.vue';
import MrtHeading from '../components/ui/MrtHeading.vue';
import MrtHtmlPanel from '../components/ui/MrtHtmlPanel.vue';
import MrtStack from '../components/ui/MrtStack.vue';
import MrtLegend from '../components/ui/MrtLegend.vue';
import MrtMonthDayCell from '../components/ui/MrtMonthDayCell.vue';
import type { MonthVueConfig } from '../config/types';
import type { MonthGridCell } from '../utils/monthGrid';
import { buildMonthGrid } from '../utils/monthGrid';
import { chunkWeekRows } from '../utils/calendarGrid';
import MrtTimetableOverviewView from '../components/overview/MrtTimetableOverviewView.vue';
import { useTimetableOverview } from '../composables/useTimetableOverview';
import { useMonthCalendar } from '../composables/useMonthCalendar';
import { useMonthDayPanel } from '../composables/useMonthDayPanel';
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

const { overview: dayOverview, loading: dayLoading, error: dayError, fetchDayOverview } =
  useTimetableOverview(props.config);

const {
  selectedYmd,
  panelVisible,
  panelRef,
  closeDayPanel,
  onDayClick,
} = useMonthDayPanel(
  dates,
  monthLoading,
  trainType,
  fetchDayOverview,
  props.config.initialDate,
  dayOverview,
  dayLoading,
);

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
  return cell.info.running
    ? 'mrt-day-cell mrt-day-cell--running'
    : 'mrt-day-cell mrt-day-cell--inactive';
}

async function onMonthShift(delta: number): Promise<void> {
  if (monthLoading.value) {
    return;
  }
  closeDayPanel();
  await shiftMonth(delta);
}
</script>

<template>
  <MrtPublicAppShell>
  <MrtStack
    as="div"
    class="mrt-month"
    margin-top="md"
    margin-bottom="md"
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

    <MrtStack v-if="monthError" margin-top="sm">
      <MrtAlert variant="error" live="assertive">
        {{ monthError }}
      </MrtAlert>
    </MrtStack>

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

    <MrtStack v-if="showLegend" margin-top="sm">
      <MrtLegend :items="legendItems" :hints="legendHints" />
    </MrtStack>

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
  </MrtStack>
  </MrtPublicAppShell>
</template>

<style scoped>
.mrt-month {
  --mrt-month-cell-min-height: 5rem;
  --mrt-month-day-font: 1.15rem;
  --mrt-month-bar-height: 0.75rem;
  --mrt-month-bar-single-height: 1.15rem;
  --mrt-radius-sm: 0;
}

.mrt-month__grid-wrap {
  position: relative;
}

.mrt-month__grid--loading {
  opacity: 0.55;
  pointer-events: none;
}

.mrt-month__grid--loading::after {
  content: "";
  position: absolute;
  inset: 0;
  cursor: wait;
}

:deep(.mrt-day-timetable) {
  margin: var(--mrt-spacing-md) 0;
}

:deep(.mrt-day-timetable-container) {
  margin-top: var(--mrt-spacing-xl);
}

@media (max-width: 40rem) {
  .mrt-month {
    --mrt-month-cell-min-height: 3.1rem;
    --mrt-month-day-font: 1.05rem;
    --mrt-month-bar-height: 0.55rem;
    --mrt-month-bar-single-height: 0.9rem;
  }

  .mrt-month__grid-wrap {
    border: 1px solid var(--mrt-border-light);
    border-radius: 0;
    overflow: hidden;
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.07);
  }
}

@media (min-width: 40.01rem) and (max-width: 56rem) {
  .mrt-month {
    --mrt-month-cell-min-height: 4.25rem;
    --mrt-month-day-font: 1.1rem;
  }
}
</style>
