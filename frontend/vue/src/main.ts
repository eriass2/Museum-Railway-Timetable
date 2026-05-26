import { createApp, type Component } from 'vue';
import MonthCalendarApp from './apps/MonthCalendarApp.vue';
import TimetableOverviewApp from './apps/TimetableOverviewApp.vue';
import JourneyWizardApp from './apps/JourneyWizardApp.vue';
import { parseMountConfig, type MrtVueApp, type MrtVueConfig } from './useMrtConfig';
import './style.css';

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

document.querySelectorAll<HTMLElement>('[data-mrt-vue-app]').forEach(mountRoot);
