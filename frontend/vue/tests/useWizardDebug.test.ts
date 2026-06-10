import { computed } from 'vue';
import { describe, expect, it } from 'vitest';
import { createWizardStore } from '../src/wizard/store/createWizardStore';
import type { WizardVueConfig } from '../src/config/types';
import { applyWizardDebugPreset } from '../src/wizard/composables/useWizardDebug';

function wizardConfig(): WizardVueConfig {
  return {
    app: 'wizard',
    stations: [],
    wizard: {
      debugPresets: {
        demo: {
          step: 'outbound',
          tripType: 'return',
          from: 1,
          to: 2,
          fromTitle: 'Alpha',
          toTitle: 'Beta',
          date: '2026-06-04',
          calendarYear: 2026,
          calendarMonth: 6,
          outbound: { service_id: 9, from_departure: '09:00', to_arrival: '10:00' },
        },
      },
    },
    labels: {},
  };
}

describe('applyWizardDebugPreset', () => {
  it('applies preset route, date and step', () => {
    const ctx = createWizardStore(wizardConfig());

    applyWizardDebugPreset(ctx, 'demo');

    expect(ctx.store.fromId).toBe(1);
    expect(ctx.store.toId).toBe(2);
    expect(ctx.store.dateYmd).toBe('2026-06-04');
    expect(ctx.store.tripType).toBe('return');
    expect(ctx.store.step).toBe('outbound');
    expect(ctx.store.calYear).toBe(2026);
    expect(ctx.store.calMonth).toBe(6);
    expect(ctx.store.outbound?.service_id).toBe(9);
  });

  it('ignores unknown preset keys', () => {
    const ctx = createWizardStore(wizardConfig());
    ctx.store.fromId = 5;

    applyWizardDebugPreset(ctx, 'missing');

    expect(ctx.store.fromId).toBe(5);
  });
});

describe('wizard store contextLine getter', () => {
  it('reflects updated route in context line', () => {
    const ctx = createWizardStore({
      ...wizardConfig(),
      wizard: {
        tripSingle: 'Enkel',
        tripReturn: 'Retur',
        monthNames: ['jan', 'feb', 'mar', 'apr', 'maj', 'jun'],
      },
    });
    ctx.store.setRoute(1, 2, 'single', 'A', 'B');
    ctx.store.dateYmd = '2026-06-01';

    expect(ctx.store.contextLine).toContain('A → B');
    expect(ctx.store.contextLine).toContain('jun');
  });
});
