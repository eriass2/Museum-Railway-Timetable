import type { AdminClientConfig } from '../../types';
import { adminStr } from '../adminLabels';

export type DashboardStatItem = {
  key: 'stations' | 'routes' | 'timetables' | 'services' | 'train_types';
  label: string;
};

/** Labels for dashboard stat counters (mobile cards and desktop summary). */
export function buildDashboardStatItems(cfg: AdminClientConfig): readonly DashboardStatItem[] {
  return [
    { key: 'stations', label: adminStr(cfg, 'dashboardStatStations', 'Stationer') },
    { key: 'routes', label: adminStr(cfg, 'dashboardStatRoutes', 'Rutter') },
    { key: 'timetables', label: adminStr(cfg, 'dashboardStatTimetables', 'Tidtabeller') },
    { key: 'services', label: adminStr(cfg, 'dashboardStatServices', 'Turer') },
    { key: 'train_types', label: adminStr(cfg, 'dashboardStatTrainTypes', 'Tågtyper') },
  ] as const;
}
