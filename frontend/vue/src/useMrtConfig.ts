export type MrtVueApp = 'month' | 'overview' | 'wizard';

export type MrtVueConfig = {
  app: MrtVueApp;
  ajaxurl?: string;
  nonce?: string;
  strings?: Record<string, string>;
  [key: string]: unknown;
};

export function parseMountConfig(el: HTMLElement): MrtVueConfig | null {
  const raw = el.getAttribute('data-mrt-config');
  if (!raw) {
    return null;
  }
  try {
    const data = JSON.parse(raw) as MrtVueConfig;
    return data && typeof data === 'object' ? data : null;
  } catch {
    return null;
  }
}
