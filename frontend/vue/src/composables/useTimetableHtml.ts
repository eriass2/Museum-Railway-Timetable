import { ref } from 'vue';
import { mrtPost } from '../api/mrtApi';
import type { MrtAjaxConfig } from '../config/types';
import { resolveMrtString } from '../utils/mrtStrings';
import { useMrtAjax } from './useMrtAjax';

/**
 * Fetch timetable HTML from WordPress AJAX.
 * HTML is server-sanitized; render only via v-html (see TRUSTED_HTML.md).
 */
export function useTimetableHtml(config: MrtAjaxConfig) {
  const html = ref('');
  const { loading, error, run, clearError } = useMrtAjax(config);

  async function fetchOverviewHtml(timetableId: number): Promise<boolean> {
    if (timetableId <= 0) {
      error.value = resolveMrtString(config, 'errorLoading', 'Tidtabell hittades inte.');
      return false;
    }
    const res = await run<{ html: string }>('mrt_timetable_overview_html', {
      timetable_id: timetableId,
    });
    if (res.success && res.data?.html) {
      html.value = res.data.html;
      return true;
    }
    return false;
  }

  async function fetchDayHtml(ymd: string, trainType = ''): Promise<boolean> {
    if (!ymd) {
      return false;
    }
    clearError();
    html.value = '';
    const res = await run<{ html: string }>('mrt_get_timetable_for_date', {
      date: ymd,
      train_type: trainType,
    });
    if (res.success && res.data?.html) {
      html.value = res.data.html;
      return true;
    }
    return false;
  }

  return { html, loading, error, fetchOverviewHtml, fetchDayHtml };
}

/** Load overview HTML without toggling useMrtAjax loading (wizard drawer). */
export async function loadOverviewHtml(
  config: MrtAjaxConfig,
  timetableId: number,
): Promise<string> {
  if (timetableId <= 0) {
    return '';
  }
  const res = await mrtPost<{ html: string }>(config, 'mrt_timetable_overview_html', {
    timetable_id: timetableId,
  });
  return res.success && res.data?.html ? res.data.html : '';
}
