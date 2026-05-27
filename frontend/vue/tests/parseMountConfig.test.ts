import { describe, expect, it } from 'vitest';
import { parseMountConfig } from '../src/config/parseMountConfig';
import { isMonthConfig, isOverviewConfig, isWizardConfig } from '../src/config/types';

function mountEl(attrs: Record<string, string>): HTMLElement {
  return {
    getAttribute: (name: string) => attrs[name] ?? null,
    querySelector: () => null,
  } as unknown as HTMLElement;
}

describe('parseMountConfig', () => {
  it('parses config from data attribute', () => {
    const el = mountEl({
      'data-mrt-config': JSON.stringify({ app: 'month', monthTitle: 'Maj 2026' }),
    });
    const cfg = parseMountConfig(el);
    expect(cfg?.app).toBe('month');
    expect(cfg && isMonthConfig(cfg) ? cfg.monthTitle : '').toBe('Maj 2026');
  });

  it('returns null for invalid JSON', () => {
    const el = mountEl({ 'data-mrt-config': '{bad' });
    expect(parseMountConfig(el)).toBeNull();
  });
});

describe('config guards', () => {
  it('narrows by app', () => {
    expect(isWizardConfig({ app: 'wizard' } as never)).toBe(true);
    expect(isOverviewConfig({ app: 'overview' } as never)).toBe(true);
    expect(isMonthConfig({ app: 'month' } as never)).toBe(true);
  });
});
