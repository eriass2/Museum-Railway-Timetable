import type { MrtVueApp, MrtVueConfig } from './types';

export function parseMountConfig(el: HTMLElement): MrtVueConfig | null {
  const script = el.querySelector('script.mrt-vue-config');
  const raw = script?.textContent?.trim() || el.getAttribute('data-mrt-config');
  if (!raw) {
    return null;
  }
  try {
    const data = JSON.parse(raw) as { app?: MrtVueApp };
    if (!data || typeof data !== 'object' || !data.app) {
      return null;
    }
    return data as MrtVueConfig;
  } catch {
    return null;
  }
}
