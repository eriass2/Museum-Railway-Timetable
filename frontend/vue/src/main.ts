import { createApp, defineAsyncComponent, type Component } from 'vue';
import MonthCalendarApp from './apps/MonthCalendarApp.vue';
import TimetableOverviewApp from './apps/TimetableOverviewApp.vue';

const JourneyWizardApp = defineAsyncComponent(() => import('./apps/JourneyWizardApp.vue'));
import { parseMountConfig, type MrtVueApp, type MrtVueConfig } from './useMrtConfig';
import './styles/mrt-public.css';

const apps: Record<MrtVueApp, Component<{ config: MrtVueConfig }>> = {
  month: MonthCalendarApp,
  overview: TimetableOverviewApp,
  wizard: JourneyWizardApp,
};

function mountRoot(el: HTMLElement): void {
  const appId = el.getAttribute('data-mrt-vue-app') as MrtVueApp | null;
  const config = parseMountConfig(el);
  if (!appId || !config || !apps[appId]) {
    return;
  }
  const App = apps[appId];
  createApp(App, { config }).mount(el);
}

function bootVueApps(): void {
  document.querySelectorAll<HTMLElement>('[data-mrt-vue-app]').forEach(mountRoot);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bootVueApps);
} else {
  bootVueApps();
}
