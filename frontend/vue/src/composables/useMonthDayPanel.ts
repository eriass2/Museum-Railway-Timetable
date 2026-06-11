import { nextTick, onMounted, ref, watch, type Ref } from 'vue';
import type { MonthDayMeta } from '../config/types';
import type { TimetableOverviewPayload } from '../types/timetableOverview';
import MrtHtmlPanel from '../components/ui/MrtHtmlPanel.vue';
import { syncDayCalendarQuery } from '../utils/monthCalendarQuery';

type DayOverviewFetcher = (ymd: string, trainType: string) => Promise<unknown>;

export function useMonthDayPanel(
  dates: Ref<Record<number, MonthDayMeta>>,
  monthLoading: Ref<boolean>,
  trainType: Ref<string>,
  fetchDayOverview: DayOverviewFetcher,
  initialDate: string | undefined,
  dayOverview: Ref<TimetableOverviewPayload | null>,
  dayLoading: Ref<boolean>,
) {
  const selectedYmd = ref('');
  const panelVisible = ref(false);
  const panelRef = ref<InstanceType<typeof MrtHtmlPanel> | null>(null);

  function isRunningDay(ymd: string): boolean {
    return Object.values(dates.value).some(
      (day) => day.ymd === ymd && Boolean(day.running),
    );
  }

  function closeDayPanel(): void {
    panelVisible.value = false;
    selectedYmd.value = '';
    syncDayCalendarQuery(null);
  }

  async function onDayClick(ymd: string): Promise<void> {
    if (!ymd || monthLoading.value) {
      return;
    }
    selectedYmd.value = ymd;
    panelVisible.value = true;
    syncDayCalendarQuery(ymd);
    await fetchDayOverview(ymd, trainType.value);
  }

  watch([panelVisible, dayOverview, dayLoading], async ([visible, overview, loading]) => {
    if (!visible || loading || !overview) {
      return;
    }
    await nextTick();
    const panel = panelRef.value;
    const el =
      panel instanceof HTMLElement
        ? panel
        : (panel as { $el?: HTMLElement } | null)?.$el;
    el?.focus();
  });

  onMounted(async () => {
    const initial = initialDate?.trim();
    if (initial && isRunningDay(initial)) {
      await onDayClick(initial);
    }
  });

  return {
    selectedYmd,
    panelVisible,
    panelRef,
    closeDayPanel,
    onDayClick,
  };
}
