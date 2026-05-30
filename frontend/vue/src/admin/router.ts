import { createRouter, createWebHashHistory } from 'vue-router';
import DashboardPage from './pages/DashboardPage.vue';
import TimetableListPage from './pages/TimetableListPage.vue';
import TimetableEditorPage from './pages/TimetableEditorPage.vue';
import StationsRoutesPage from './pages/StationsRoutesPage.vue';

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
    ],
  });

  const map: Record<string, string> = {
    dashboard: '/dashboard',
    timetables: '/timetables',
    'stations-routes': '/stations-routes',
  };
  const target = map[initialRoute] ?? '/dashboard';
  void router.replace(target);

  return router;
}
