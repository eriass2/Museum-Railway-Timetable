import { ref } from 'vue';
import type { MrtRestConfig } from '../config/types';
import type { TimetableOverviewPayload } from '../types/timetableOverview';
import { resolveMrtString } from '../utils/mrtStrings';
import { useMrtRest } from './useMrtRest';

export function useTimetableOverview(config: MrtRestConfig) {
  const overview = ref<TimetableOverviewPayload | null>(null);
  const { loading, error, run, clearError } = useMrtRest(config);

  async function fetchOverview(timetableId: number): Promise<boolean> {
    if (timetableId <= 0) {
      error.value = resolveMrtString(config, 'errorLoading', 'Tidtabell hittades inte.');
      return false;
    }
    clearError();
    overview.value = null;
    const res = await run<{ overview: TimetableOverviewPayload }>('mrt_timetable_overview_data', {
      timetable_id: timetableId,
    });
    if (res.success && res.data?.overview) {
      overview.value = res.data.overview;
      return true;
    }
    return false;
  }

  async function fetchDayOverview(dateYmd: string, trainType = ''): Promise<boolean> {
    if ( !dateYmd ) {
      return false;
    }
    clearError();
    overview.value = null;
    const res = await run<{ overview: TimetableOverviewPayload }>('mrt_get_timetable_for_date', {
      date: dateYmd,
      train_type: trainType,
    });
    if (res.success && res.data?.overview) {
      overview.value = res.data.overview;
      return true;
    }
    return false;
  }

  return { overview, loading, error, fetchOverview, fetchDayOverview };
}
