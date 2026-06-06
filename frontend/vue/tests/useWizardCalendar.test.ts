/**
 * @vitest-environment happy-dom
 */
import { computed, ref } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createWizardStore } from '../src/wizard/store/createWizardStore';
import type { WizardVueConfig } from '../src/config/types';
import { useWizardCalendarView } from '../src/wizard/composables/wizardCalendarView';

vi.mock('../src/wizard/composables/wizardCalendarLoad', async (importOriginal) => {
  const actual = await importOriginal<typeof import('../src/wizard/composables/wizardCalendarLoad')>();
  return {
    ...actual,
    initWizardCalendar: vi.fn(),
    loadWizardCalendarMonth: vi.fn(),
  };
});

import { initWizardCalendar } from '../src/wizard/composables/wizardCalendarLoad';
import { useWizardCalendar } from '../src/wizard/composables/useWizardCalendar';
import { withSetup } from './helpers/withSetup';

function wizardConfig(): WizardVueConfig {
  return {
    app: 'wizard',
    restUrl: 'http://example.test/wp-json/museum-railway-timetable/v1/',
    restNonce: 'nonce',
    startOfWeek: 1,
    wizard: {
      monthNames: ['januari', 'februari', 'mars', 'april', 'maj', 'juni'],
      weekdayAbbrev: ['m', 't', 'o', 't', 'f', 'l', 's'],
    },
    labels: {},
  };
}

describe('useWizardCalendarView', () => {
  it('builds month title and bookable day count', () => {
    const ctx = createWizardStore(wizardConfig());
    ctx.store.calYear = 2026;
    ctx.store.calMonth = 6;
    const daysMap = ref<Record<string, { status: 'ok' }>>({
      '2026-06-04': { status: 'ok' },
      '2026-06-05': { status: 'ok' },
    });

    const view = useWizardCalendarView(ctx.store, ctx.cfg, daysMap, 1);

    expect(view.monthTitle.value.toLowerCase()).toContain('juni');
    expect(view.hasBookableDays.value).toBe(true);
    expect(view.weekdayHeaders.value.length).toBe(7);
    expect(view.gridRows.value.length).toBeGreaterThan(0);
  });
});

describe('useWizardCalendar', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('exposes calendar view helpers after mount', () => {
    const ctx = createWizardStore(wizardConfig());
    ctx.store.calYear = 2026;
    ctx.store.calMonth = 6;

    const { result: cal, unmount } = withSetup(() =>
      useWizardCalendar(ctx.store, wizardConfig(), ctx.cfg),
    );

    expect(initWizardCalendar).toHaveBeenCalled();
    expect(cal.monthTitle).toBeDefined();
    expect(cal.onPickDate).toBeTypeOf('function');
    expect(cal.shiftMonth).toBeTypeOf('function');

    unmount();
  });
});
