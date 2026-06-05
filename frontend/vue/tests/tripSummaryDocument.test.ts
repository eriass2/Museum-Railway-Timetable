import { describe, expect, it } from 'vitest';
import { buildTripSummaryHtml, tripSummaryPdfStyles } from '../src/wizard/utils/tripSummaryDocument';

const sampleInput = {
  title: 'Din resa',
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
  });
});
