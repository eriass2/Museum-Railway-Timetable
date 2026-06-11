import type { AdminClientConfig, TrafficToday } from '../../types';
import { adminFmtN, adminStr } from '../adminLabels';

/** User-facing summary for today's traffic panel. */
export function trafficTodayStatusText(cfg: AdminClientConfig, traffic: TrafficToday): string {
  if (traffic.services_count === 0) {
    return adminStr(cfg, 'trafficTodayNoServices');
  }
  if (traffic.all_cancelled) {
    return adminFmtN(cfg, 'trafficTodayAllCancelled', { 1: traffic.services_count });
  }
  return adminFmtN(cfg, 'trafficTodaySummary', {
    1: traffic.services_count,
    2: traffic.timetable_title,
  });
}
