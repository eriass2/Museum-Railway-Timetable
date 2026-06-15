import { describe, expect, it } from 'vitest';
import { buildTripSummaryHtml, tripSummaryPdfStyles } from '../src/wizard/utils/tripSummaryDocument';

const sampleInput = {
  title: 'Din resa',
  downloadName: 'Uppsala → Fjällnora',
  tripTypeLabel: 'Enkel resa',
  legs: [
    {
      heading: 'Utresa',
      route: 'Uppsala → Fjällnora',
      timeRange: '11:10 – 11:57',
      date: 'onsdag 1 juli 2026',
    },
  ],
};

describe('tripSummaryDocument', () => {
  it('builds escaped HTML body and shared PDF styles', () => {
    const body = buildTripSummaryHtml(sampleInput);
    expect(body).toContain('<h1>Din resa</h1>');
    expect(body).toContain('Uppsala → Fjällnora');
    expect(tripSummaryPdfStyles()).toContain('@page');
    expect(tripSummaryPdfStyles()).not.toMatch(/\.prices\s*\{[^}]*break-inside:\s*avoid/);
  });

  it('combines route and date on one line', () => {
    const body = buildTripSummaryHtml(sampleInput);
    expect(body).toContain('Uppsala → Fjällnora · onsdag 1 juli 2026');
    expect(body).not.toContain('<p class="date">');
  });

  it('uses a table for prices so labels and values stay separated', () => {
    const body = buildTripSummaryHtml({
      ...sampleInput,
      priceSection: {
        heading: 'Priser',
        ticketTypeLabel: 'Enkelbiljett',
        rows: [
          { label: 'Vuxen', value: '80 kr' },
          { label: 'Barn 4–15', value: '30 kr' },
        ],
      },
    });
    expect(body).toContain('<table class="price-table">');
    expect(body).toContain('<td class="price-label">Vuxen</td>');
    expect(body).toContain('<td class="price-value">80 kr</td>');
  });

  it('omits leg segments for a direct single-leg trip', () => {
    const body = buildTripSummaryHtml({
      ...sampleInput,
      legs: [
        {
          heading: 'Utresa',
          route: 'Uppsala Östra → Gunsta',
          timeRange: '16.45 – 17.04',
          date: '5 juni 2026',
          segments: [
            {
              type: 'leg',
              leg: {
                vehicleLabel: 'Rälsbuss 101 mot Gunsta',
                iconUrl: '',
                kind: 'train',
                timeRange: '16.45 – 17.04',
                route: 'Uppsala Östra → Gunsta',
              },
            },
          ],
        },
      ],
    });
    expect(body).not.toContain('<ul class="segments">');
  });

  it('lays out trip and day ticket prices side by side when both exist', () => {
    const body = buildTripSummaryHtml({
      ...sampleInput,
      priceSection: {
        heading: 'Priser',
        ticketTypeLabel: 'Returbiljett',
        rows: [{ label: 'Vuxen', value: '160 kr' }],
        dayTicketHeading: 'Heldagsbiljett',
        dayTicketRows: [{ label: 'Vuxen', value: '280 kr' }],
      },
    });
    expect(body).toContain('<table class="price-columns-table">');
    expect(body).toContain('Returbiljett');
    expect(body).toContain('Heldagsbiljett');
  });

  it('renders each price footnote as its own paragraph', () => {
    const body = buildTripSummaryHtml({
      ...sampleInput,
      priceSection: {
        heading: 'Priser',
        ticketTypeLabel: 'Enkelbiljett',
        rows: [{ label: 'Vuxen', value: '80 kr' }],
        notes: ['Zonförklaring.', 'Stationstext.'],
      },
    });
    expect(body.match(/<p class="note">/g)).toHaveLength(2);
    expect(body).toContain('Zonförklaring.');
    expect(body).toContain('Stationstext.');
  });

  it('lays out outbound and return legs side by side for round trips', () => {
    const body = buildTripSummaryHtml({
      ...sampleInput,
      tripTypeLabel: 'Tur och retur',
      legs: [
        {
          heading: 'Utresa',
          route: 'Uppsala Östra → Gunsta',
          timeRange: '10.00 – 10.24',
          date: '13 juni 2026',
        },
        {
          heading: 'Återresa',
          route: 'Gunsta → Uppsala Östra',
          timeRange: '11.50 – 12.17',
          date: '13 juni 2026',
        },
      ],
    });
    expect(body).toContain('<table class="legs-table">');
    expect(body).toContain('<td class="legs-cell">');
    expect(body).toContain('Utresa');
    expect(body).toContain('Återresa');
  });
});
