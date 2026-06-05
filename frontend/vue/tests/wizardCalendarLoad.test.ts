import { computed, ref } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createWizardStore } from '../src/wizard/store/createWizardStore';
import type { WizardVueConfig } from '../src/config/types';
import type { CalendarDayInfo, CalendarDayStatus } from '../src/shared/calendarDay';
import type { WizardCfg } from '../src/wizard/utils/wizardCfgTypes';
import {
  loadWizardCalendarMonth,
  pickWizardCalendarDate,
  wizardCalendarDayAria,
} from '../src/wizard/composables/wizardCalendarLoad';

const config: WizardVueConfig = {
  app: 'wizard',
  restUrl: 'http://example.test/wp-json/museum-railway-timetable/v1/',
  restNonce: 'nonce',
  startOfWeek: 1,
  wizard: {
    monthNames: ['januari', 'februari', 'mars', 'april', 'maj', 'juni'],
  },
  strings: {
    errorGeneric: 'Något gick fel.',
    dayDateOk: 'Datum %s, trafik finns',
    dayDateTraffic: 'Datum %s, ingen match',
    dayDateNone: 'Datum %s, ingen trafik',
  },
};

describe('wizardCalendarLoad', () => {
  beforeEach(() => {
    vi.restoreAllMocks();
  });

  it('pickWizardCalendarDate stores date and advances step', () => {
    const { store } = createWizardStore(config);
    pickWizardCalendarDate(store, '2026-06-15');

    expect(store.dateYmd).toBe('2026-06-15');
    expect(store.step).toBe('outbound');
  });

  it('wizardCalendarDayAria labels bookable days', () => {
    const cfg = computed(
      (): WizardCfg => ({
        monthNames: ['januari', 'februari', 'mars', 'april', 'maj', 'juni'],
        dayDateOk: config.strings?.dayDateOk ?? '',
        dayDateTraffic: config.strings?.dayDateTraffic ?? '',
        dayDateNone: config.strings?.dayDateNone ?? '',
      }),
    );
    const aria = wizardCalendarDayAria('2026-06-04', 'ok', cfg);

    expect(aria).toContain('juni');
    expect(aria).toContain('trafik finns');
  });

  it('loadWizardCalendarMonth stores days from REST', async () => {
    const { store } = createWizardStore(config);
    store.fromId = 1;
    store.toId = 2;
    store.tripType = 'return';
    const daysMap = ref<Record<string, CalendarDayInfo | CalendarDayStatus>>({});
    const run = vi.fn().mockResolvedValue({
      success: true,
      data: {
        year: 2026,
        month: 6,
        days: {
          '2026-06-04': { status: 'ok', types: ['green'] },
        },
      },
    });
    const cfg = computed(() => ({
      errorGeneric: config.strings?.errorGeneric ?? '',
    }));

    await loadWizardCalendarMonth(store, cfg, daysMap, run, 2026, 6);

    expect(store.calYear).toBe(2026);
    expect(store.calMonth).toBe(6);
    expect(daysMap.value['2026-06-04']).toEqual({ status: 'ok', types: ['green'] });
    expect(run).toHaveBeenCalledWith('mrt_journey_calendar_month', {
      from_station: 1,
      to_station: 2,
      year: 2026,
      month: 6,
      trip_type: 'return',
    });
  });
});
