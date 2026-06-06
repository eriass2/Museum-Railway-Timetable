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
    {
      id: 'prices',
      label: adminStr(cfg, 'setupStepPrices'),
      done: (stats.prices_configured ?? 0) > 0,
      route: '/prices',
    },
    {
      id: 'station_zones',
      label: adminStr(cfg, 'setupStepStationZones'),
      done:
        (stats.stations ?? 0) > 0 && (stats.stations_without_zones ?? 0) === 0,
      route: '/stations-routes',
    },
  ];
}

export function isAdminSetupComplete(steps: AdminSetupStep[]): boolean {
  return steps.every((s) => s.done);
}
