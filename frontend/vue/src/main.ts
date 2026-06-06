import { createApp, type Component } from 'vue';
import { parseMountConfig, type MrtVueApp, type MrtVueConfig } from './config';
import './styles/mrt-public.css';

type AppLoader = () => Promise<Component<{ config: MrtVueConfig }>>;

const appLoaders: Record<MrtVueApp, AppLoader> = {
  month: () => import('./apps/MonthCalendarApp.vue').then((m) => m.default),
  overview: () => import('./apps/TimetableOverviewApp.vue').then((m) => m.default),
  wizard: () => import('./apps/JourneyWizardApp.vue').then((m) => m.default),
  index: () => import('./apps/TimetableIndexApp.vue').then((m) => m.default),
};

async function mountRoot(el: HTMLElement): Promise<void> {
  const appId = el.getAttribute('data-mrt-vue-app') as MrtVueApp | null;
  const config = parseMountConfig(el);
  if (!appId || !config || !appLoaders[appId]) {
    return;
  }
  const App = await appLoaders[appId]();
  createApp(App, { config }).mount(el);
}

function bootVueApps(): void {
  document.querySelectorAll<HTMLElement>('[data-mrt-vue-app]').forEach((el) => {
    void mountRoot(el);
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bootVueApps);
} else {
  bootVueApps();
}
