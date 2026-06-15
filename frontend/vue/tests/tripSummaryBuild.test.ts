import { describe, expect, it } from 'vitest';
import { buildTripSummaryInput, buildTripSummaryLegs } from '../src/wizard/utils/tripSummaryBuild';
import type { WizardStore } from '../src/wizard/store/wizardStoreTypes';

const storeStub = {
  fromTitle: 'Uppsala Östra',
  toTitle: 'Fjällnora',
  dateYmd: '2026-06-05',
  tripType: 'single',
  config: { stations: [{ id: 1, title: 'Uppsala Östra' }] },
  outbound: {
    service_id: 1,
    from_departure: '10:00',
    to_arrival: '11:25',
    legs: [
      {
        service_id: 71,
        service_number: '71',
        train_type: 'Ångtåg',
        from_station_id: 1,
        to_station_id: 2,
        from_departure: '10:00',
        to_arrival: '10:35',
        destination: 'Marielund',
      },
    ],
  },
  inbound: null,
} as unknown as WizardStore;

describe('tripSummaryBuild', () => {
  it('builds legs from store selections', () => {
    const legs = buildTripSummaryLegs(storeStub, {}, '6 juni 2026');
    expect(legs).toHaveLength(1);
    expect(legs[0].route).toBe('Uppsala Östra → Fjällnora');
    expect(legs[0].timeRange).toBe('10.00 – 11.25');
  });

  it('builds PDF/text input with title and legs', () => {
    const input = buildTripSummaryInput({
      store: storeStub,
      cfg: { stepSummary: 'Din resa', outboundHeading: 'Utresa' },
      dateText: '6 juni 2026',
      tripTypeLabel: 'Enkel resa',
      priceData: null,
      dayPrices: null,
      priceLabels: { categories: {}, tickets: {}, dash: '—', title: '', titleSuffix: '', note: '', typeColumnSr: '' },
    });
    expect(input.title).toBe('Din resa');
    expect(input.downloadName).toBe('Uppsala Östra → Fjällnora 2026-06-05');
    expect(input.legs[0].heading).toBe('Utresa');
    expect(input.priceSection).toBeUndefined();
  });

  it('includes zone and senior price footnotes in export note (J16)', () => {
    const input = buildTripSummaryInput({
      store: storeStub,
      cfg: {},
      dateText: '6 juni 2026',
      tripTypeLabel: 'Enkel resa',
      priceData: {
        activeType: 'single',
        isAfternoonReturn: false,
        matrix: { single: { adult: 120 } },
      },
      dayPrices: null,
      priceLabels: {
        categories: { adult: 'Vuxen' },
        tickets: { single: 'Enkel' },
        dash: '—',
        title: 'Priser',
        titleSuffix: '',
        note: 'Biljettpriset beror på hur många zoner din resa går igenom.',
        seniorNote: 'Pensionär gäller från 65 år.',
        typeColumnSr: '',
      },
    });
    expect(input.priceSection?.notes).toContain('Biljettpriset beror på hur många zoner din resa går igenom.');
    expect(input.priceSection?.notes).toContain('Pensionär gäller från 65 år.');
  });

  it('includes station purchase and ticket copy footnotes in export notes (J15)', () => {
    const input = buildTripSummaryInput({
      store: storeStub,
      cfg: {},
      dateText: '6 juni 2026',
      tripTypeLabel: 'Enkel resa',
      priceData: {
        activeType: 'single',
        isAfternoonReturn: false,
        matrix: { single: { adult: 120 } },
      },
      dayPrices: null,
      priceLabels: {
        categories: { adult: 'Vuxen' },
        tickets: { single: 'Enkel' },
        dash: '—',
        title: 'Priser',
        titleSuffix: '',
        note: '',
        typeColumnSr: '',
        stationPurchaseNote: 'Din resa börjar på Uppsala Östra.',
        footnotes: ['Biljetterna gäller hela trafiksäsongen.'],
      },
    });
    expect(input.priceSection?.notes).toContain('Din resa börjar på Uppsala Östra.');
    expect(input.priceSection?.notes).toContain('Biljetterna gäller hela trafiksäsongen.');
  });
});
