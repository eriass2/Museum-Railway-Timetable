import { describe, expect, it } from 'vitest';
import { buildWizardCalendarGrid } from '../src/wizard/utils/wizardCalendarGrid';

describe('buildWizardCalendarGrid', () => {
  it('pads month start and marks day status from map', () => {
    const rows = buildWizardCalendarGrid(2026, 5, 1, { '2026-05-15': 'ok' });
    const days = rows.flat().filter((c) => c.kind === 'day');
    expect(days).toHaveLength(31);
    const mid = days.find((c) => c.kind === 'day' && c.ymd === '2026-05-15');
    expect(mid && mid.kind === 'day' ? mid.status : '').toBe('ok');
  });

  it('defaults missing days to none', () => {
    const rows = buildWizardCalendarGrid(2026, 5, 1, {});
    const first = rows.flat().find((c) => c.kind === 'day');
    expect(first && first.kind === 'day' ? first.status : '').toBe('none');
  });
});
