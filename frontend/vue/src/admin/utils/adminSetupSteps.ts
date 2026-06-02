import type { AdminClientConfig } from '../types';
import { adminStr } from './adminLabels';

export type AdminSetupStep = {
  id: string;
  label: string;
  done: boolean;
  route: string;
};

export function buildAdminSetupSteps(
  stats: Record<string, number>,
  cfg: AdminClientConfig,
): AdminSetupStep[] {
  return [
    {
      id: 'stations',
      label: adminStr(cfg, 'setupStepStations'),
      done: (stats.stations ?? 0) > 0,
      route: '/stations-routes',
    },
    {
      id: 'routes',
      label: adminStr(cfg, 'setupStepRoutes'),
      done: (stats.routes ?? 0) > 0,
      route: '/stations-routes',
    },
    {
      id: 'timetables',
      label: adminStr(cfg, 'setupStepTimetables'),
      done: (stats.timetables ?? 0) > 0,
      route: '/timetables',
    },
    {
      id: 'services',
      label: adminStr(cfg, 'setupStepServices'),
      done: (stats.services ?? 0) > 0,
      route: '/timetables',
    },
  ];
}

export function isAdminSetupComplete(steps: AdminSetupStep[]): boolean {
  return steps.every((s) => s.done);
}
