import { createRouter, createWebHashHistory } from 'vue-router';
import DashboardPage from './pages/DashboardPage.vue';
import TimetableListPage from './pages/TimetableListPage.vue';
import TimetableEditorPage from './pages/TimetableEditorPage.vue';
import StationsRoutesPage from './pages/StationsRoutesPage.vue';
import SettingsPage from './pages/SettingsPage.vue';
import PricesPage from './pages/PricesPage.vue';
import TrainTypesPage from './pages/TrainTypesPage.vue';
import ImportExportPage from './pages/ImportExportPage.vue';
import DevToolsPage from './pages/DevToolsPage.vue';
import HelpPage from './pages/HelpPage.vue';
import ShortcodesPage from './pages/ShortcodesPage.vue';
import TrafficNoticesPage from './pages/TrafficNoticesPage.vue';
import { adminConfig } from './types';

export function createAdminRouter(initialRoute: string) {
  const cfg = adminConfig();
  const routes = [
    { path: '/', redirect: '/dashboard' },
    { path: '/dashboard', component: DashboardPage, name: 'dashboard' },
    { path: '/timetables', component: TimetableListPage, name: 'timetables' },
    {
      path: '/traffic-notices',
      component: TrafficNoticesPage,
      name: 'traffic-notices',
    },
    {
      path: '/timetables/:id',
      component: TimetableEditorPage,
      name: 'timetable-edit',
      props: true,
    },
    {
      path: '/stations-routes',
      component: StationsRoutesPage,
      name: 'stations-routes',
    },
    { path: '/settings', component: SettingsPage, name: 'settings' },
    { path: '/prices', component: PricesPage, name: 'prices' },
    { path: '/train-types', component: TrainTypesPage, name: 'train-types' },
    { path: '/import-export', component: ImportExportPage, name: 'import-export' },
    { path: '/shortcodes', component: ShortcodesPage, name: 'shortcodes' },
    { path: '/help', component: HelpPage, name: 'help' },
  ];
  if (cfg.isDevMode) {
    routes.push({ path: '/dev-tools', component: DevToolsPage, name: 'dev-tools' });
  }

  const router = createRouter({
    history: createWebHashHistory(),
    routes,
  });

  const map: Record<string, string> = {
    dashboard: '/dashboard',
    timetables: '/timetables',
    'traffic-notices': '/traffic-notices',
    'stations-routes': '/stations-routes',
    settings: '/settings',
    prices: '/prices',
    'train-types': '/train-types',
    'import-export': '/import-export',
    shortcodes: '/shortcodes',
    'dev-tools': '/dev-tools',
    help: '/help',
  };
  const hashPath = window.location.hash.replace(/^#/, '');
  const fromHash = hashPath && hashPath !== '/' ? hashPath : '';
  const target = fromHash || map[initialRoute] || '/dashboard';
  void router.replace(target);

  return router;
}
