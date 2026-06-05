import { afterEach, describe, expect, it, vi } from 'vitest';
import {
  buildTripSummaryHtml,
  canShareHtmlFile,
  openSummaryPrintTab,
  prefersStandalonePrintTab,
  shareTripSummary,
  wrapTripSummaryDocument,
} from '../src/wizard/utils/tripSummaryDocument';
import { shareTripSummaryText } from '../src/wizard/utils/tripSummaryText';

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
  priceSection: {
    heading: 'Priser',
    ticketTypeLabel: 'Enkel resa',
    rows: [{ label: 'Vuxen', value: '110 kr' }],
  },
};

describe('tripSummaryDocument', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
    vi.restoreAllMocks();
  });

  it('builds escaped HTML body and full document', () => {
    const body = buildTripSummaryHtml(sampleInput);
    expect(body).toContain('<h1>Din resa</h1>');
    expect(body).toContain('Uppsala → Fjällnora');
    expect(body).toContain('Vuxen');

    const doc = wrapTripSummaryDocument(body, 'Din resa');
    expect(doc).toContain('<!DOCTYPE html>');
    expect(doc).toContain('<title>Din resa</title>');
  });

  it('prefers standalone print tab on narrow viewports', () => {
    vi.stubGlobal('window', {
      matchMedia: vi.fn(() => ({ matches: true })),
    });
    expect(prefersStandalonePrintTab()).toBe(true);
  });

  it('opens new tab and triggers print', () => {
    const write = vi.fn();
    const print = vi.fn();
    const focus = vi.fn();
    vi.stubGlobal('window', {
      open: vi.fn(() => ({
        document: { open: vi.fn(), write, close: vi.fn(), title: '', readyState: 'complete' },
        focus,
        print,
        addEventListener: vi.fn(),
      })),
    });

    const ok = openSummaryPrintTab('<html></html>', 'Din resa');
    expect(ok).toBe(true);
    expect(write).toHaveBeenCalled();
    expect(print).toHaveBeenCalled();
  });

  it('shares HTML file when supported', async () => {
    const share = vi.fn().mockResolvedValue(undefined);
    const canShare = vi.fn(() => true);
    vi.stubGlobal('navigator', { share, canShare });

    const doc = wrapTripSummaryDocument(buildTripSummaryHtml(sampleInput), 'Din resa');
    expect(canShareHtmlFile(doc, 'Din resa')).toBe(true);

    const result = await shareTripSummary('Din resa', 'plain', doc, shareTripSummaryText);
    expect(result).toBe('shared');
    expect(share).toHaveBeenCalledWith(
      expect.objectContaining({
        title: 'Din resa',
        files: expect.any(Array),
      }),
    );
  });
});
