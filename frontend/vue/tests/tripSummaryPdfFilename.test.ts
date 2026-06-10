import { describe, expect, it } from 'vitest';
import { tripSummaryPdfFilename } from '../src/wizard/utils/downloadTripSummaryPdf';

describe('tripSummaryPdfFilename', () => {
  it('slugifies route name for download', () => {
    expect(tripSummaryPdfFilename('Uppsala Östra → Fjällnora')).toBe('uppsala-ostra-fjallnora.pdf');
  });

  it('includes trip date in filename', () => {
    expect(tripSummaryPdfFilename('Uppsala Östra → Gunsta 2026-06-05')).toBe(
      'uppsala-ostra-gunsta-2026-06-05.pdf',
    );
  });

  it('falls back when name is empty', () => {
    expect(tripSummaryPdfFilename('   ')).toBe('resa.pdf');
  });
});
