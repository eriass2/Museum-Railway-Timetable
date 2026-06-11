import type { TimetableDetail } from '../../types';
import type { DeviationRow } from '../timetable-editor/deviationsPayload';

export type MobileTrafficTodayPayload = {
  date: string;
  timetable_id: number;
  timetable_title: string;
  services_count: number;
  cancelled_count: number;
  all_cancelled: boolean;
};

/** Derive today's traffic summary from deviation rows for mobile cancel panel. */
export function buildMobileTrafficTodayPayload(
  trafficToday: string,
  timetableId: number,
  detail: TimetableDetail,
  deviationRows: DeviationRow[],
  cancelledNoticeLower: string,
): MobileTrafficTodayPayload {
  const cancelled = deviationRows.filter(
    (row) =>
      row.date === trafficToday &&
      row.notice.toLowerCase().includes(cancelledNoticeLower),
  ).length;
  const total = detail.services.length;
  return {
    date: trafficToday,
    timetable_id: timetableId,
    timetable_title: detail.title,
    services_count: total,
    cancelled_count: cancelled,
    all_cancelled: total > 0 && cancelled >= total,
  };
}
