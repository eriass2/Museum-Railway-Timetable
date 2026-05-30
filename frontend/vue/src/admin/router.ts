import { createRouter, createWebHashHistory } from 'vue-router';
import DashboardPage from './pages/DashboardPage.vue';
import TimetableListPage from './pages/TimetableListPage.vue';
import TimetableEditorPage from './pages/TimetableEditorPage.vue';
import StationsRoutesPage from './pages/StationsRoutesPage.vue';
import SettingsPage from './pages/SettingsPage.vue';
import PricesPage from './pages/PricesPage.vue';
import TrainTypesPage from './pages/TrainTypesPage.vue';
import ImportExportPage from './pages/ImportExportPage.vue';

export function createAdminRouter(initialRoute: string) {
  const router = createRouter({
    history: createWebHashHistory(),
    routes: [
      { path: '/', redirect: '/dashboard' },
      { path: '/dashboard', component: DashboardPage, name: 'dashboard' },
      { path: '/timetables', component: TimetableListPage, name: 'timetables' },
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
    ],
  });

  const map: Record<string, string> = {
    dashboard: '/dashboard',
    timetables: '/timetables',
    'stations-routes': '/stations-routes',
    settings: '/settings',
    prices: '/prices',
    'train-types': '/train-types',
    'import-export': '/import-export',
  };
  const target = map[initialRoute] ?? '/dashboard';
  void router.replace(target);

  return router;
}
