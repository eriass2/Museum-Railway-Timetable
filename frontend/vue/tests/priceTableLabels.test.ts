import { describe, expect, it } from 'vitest';
import { priceTableLabelsFromCfg } from '../src/wizard/utils/priceTableLabels';

describe('priceTableLabelsFromCfg', () => {
  it('builds title and zone suffix when showZoneCount is true', () => {
    const labels = priceTableLabelsFromCfg(
      {
        priceTitle: 'Priser',
        priceZoneLabel: '%d zoner',
        priceDash: '—',
        priceTickets: { single: 'Enkel' },
        priceCategories: { adult: 'Vuxen' },
      },
      3,
      true,
    );
    expect(labels.title).toBe('Priser');
    expect(labels.titleSuffix).toBe('(zon A–C)');
    expect(labels.tickets.single).toBe('Enkel');
    expect(labels.categories.adult).toBe('Vuxen');
  });

  it('omits zone suffix when showZoneCount is false', () => {
    const labels = priceTableLabelsFromCfg({ priceTitle: 'Priser' }, 2, false);
    expect(labels.titleSuffix).toBe('');
  });
});
