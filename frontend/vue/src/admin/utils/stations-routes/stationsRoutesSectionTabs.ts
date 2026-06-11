import type { AdminClientConfig } from '../../types';
import { adminStr } from '../adminLabels';

export type StationsRoutesSectionTab = 'stations' | 'lines' | 'routes';

export type StationsRoutesSectionTabEntry = {
  id: StationsRoutesSectionTab;
  label: string;
};

/** Section nav tabs for the stations & routes admin page. */
export function buildStationsRoutesSectionTabs(
  cfg: AdminClientConfig,
  hasLineRegistry: boolean,
): StationsRoutesSectionTabEntry[] {
  const tabs: StationsRoutesSectionTabEntry[] = [
    { id: 'stations', label: adminStr(cfg, 'stationsTabStations') },
  ];
  if (hasLineRegistry) {
    tabs.push({ id: 'lines', label: adminStr(cfg, 'stationsTabLines') });
  } else {
    tabs.push({ id: 'routes', label: adminStr(cfg, 'stationsTabRoutes') });
  }
  return tabs;
}
