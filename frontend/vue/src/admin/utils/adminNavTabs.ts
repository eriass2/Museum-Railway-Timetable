import type { AdminClientConfig } from '../types';
import { adminStr } from './adminLabels';

export type AdminNavTab = {
  to: string;
  label: string;
};

/** Build sidebar tabs from capability flags and l10n config. */
export function buildAdminNavTabs(cfg: AdminClientConfig): AdminNavTab[] {
  const items: AdminNavTab[] = [
    { to: '/dashboard', label: adminStr(cfg, 'navOverview', 'Översikt') },
    { to: '/stations-routes', label: adminStr(cfg, 'navStationsRoutes', 'Stationer & rutter') },
    { to: '/timetables', label: adminStr(cfg, 'navTimetables', 'Tidtabeller') },
  ];
  if (cfg.canOperate) {
    items.push({
      to: '/traffic-notices',
      label: adminStr(cfg, 'navTrafficNotices', 'Trafikmeddelanden'),
    });
  }
  items.push(
    { to: '/shortcodes', label: adminStr(cfg, 'navShortcodes', 'Shortcodes') },
    { to: '/help', label: adminStr(cfg, 'navHelp', 'Hjälp') },
  );
  if (cfg.canManage) {
    items.push(
      { to: '/train-types', label: adminStr(cfg, 'navTrainTypes', 'Tågtyper') },
      { to: '/settings', label: adminStr(cfg, 'navSettings', 'Inställningar') },
      { to: '/feedback', label: adminStr(cfg, 'navFeedback', 'Feedback') },
      { to: '/prices', label: adminStr(cfg, 'navPrices', 'Priser') },
      { to: '/import-export', label: adminStr(cfg, 'navImportExport', 'Import/export') },
    );
  }
  if (cfg.canManage && cfg.isDevMode) {
    items.push({ to: '/dev-tools', label: adminStr(cfg, 'navDev', 'Dev') });
  }
  return items;
}

const PREFIX_ACTIVE_PATHS = ['/timetables', '/traffic-notices'] as const;

/** Whether a nav tab should show as active for the current route path. */
export function isAdminNavTabActive(path: string, routePath: string): boolean {
  if (PREFIX_ACTIVE_PATHS.includes(path as (typeof PREFIX_ACTIVE_PATHS)[number])) {
    return routePath.startsWith(path);
  }
  return routePath === path || routePath.startsWith(`${path}/`);
}
