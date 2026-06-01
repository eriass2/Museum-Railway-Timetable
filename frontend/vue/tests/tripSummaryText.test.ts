import { describe, expect, it } from 'vitest';
import { buildTripSummaryText } from '../src/wizard/utils/tripSummaryText';

describe('buildTripSummaryText', () => {
  it('formats legs and prices as plain text', () => {
    const text = buildTripSummaryText({
      title: 'Din resa',
      tripTypeLabel: 'Tur och retur',
      legs: [
        {
          heading: 'Utresa',
          route: 'Uppsala → Marielund',
          timeRange: '10:00 – 10:45',
          date: 'lördag 6 juni 2026',
        },
      ],
      priceSection: {
        heading: 'Priser',
        ticketTypeLabel: 'Returbiljett',
        rows: [
          { label: 'Vuxen', value: '220 kr' },
          { label: 'Barn 4–15', value: '60 kr' },
        ],
        note: 'Priserna är vägledande.',
      },
    });

    expect(text).toContain('Din resa');
    expect(text).toContain('Tur och retur');
    expect(text).toContain('Utresa');
    expect(text).toContain('Uppsala → Marielund');
    expect(text).toContain('Vuxen: 220 kr');
    expect(text).toContain('Priserna är vägledande.');
  });

  it('omits price block when empty', () => {
    const text = buildTripSummaryText({
      title: 'Din resa',
      tripTypeLabel: 'Enkel resa',
      legs: [
        {
          heading: 'Utresa',
          route: 'A → B',
          timeRange: '09:00 – 10:00',
          date: 'måndag 1 juni 2026',
        },
      ],
    });

    expect(text).not.toContain('Priser');
    expect(text).toContain('Enkel resa');
  });
});
