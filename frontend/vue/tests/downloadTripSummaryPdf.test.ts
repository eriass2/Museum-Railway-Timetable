import { afterEach, describe, expect, it, vi } from 'vitest';
import { downloadTripSummaryPdf } from '../src/wizard/utils/downloadTripSummaryPdf';
import * as mrtRest from '../src/api/mrtRest';

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

describe('downloadTripSummaryPdf', () => {
  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('returns server error message on REST failure', async () => {
    vi.stubGlobal('document', { createElement: vi.fn() });
    vi.spyOn(mrtRest, 'mrtRestRequest').mockResolvedValue({
      success: false,
      message: 'PDF kunde inte skapas (e2e)',
    });

    const result = await downloadTripSummaryPdf(sampleInput, { restUrl: '/wp-json/test/v1/' });
    expect(result).toEqual({ ok: false, message: 'PDF kunde inte skapas (e2e)' });
  });

  it('triggers download when server returns base64 PDF', async () => {
    const click = vi.fn();
    vi.spyOn(mrtRest, 'mrtRestRequest').mockResolvedValue({
      success: true,
      data: {
        filename: 'resa.pdf',
        content_base64: btoa('%PDF-1.4'),
      },
    });
    vi.stubGlobal('document', {
      createElement: () => ({ click, href: '', download: '' }),
    });
    vi.stubGlobal('URL', {
      createObjectURL: () => 'blob:test',
      revokeObjectURL: vi.fn(),
    });
    vi.stubGlobal('atob', (value: string) => value);

    const result = await downloadTripSummaryPdf(sampleInput, { restUrl: '/wp-json/test/v1/' });
    expect(result).toEqual({ ok: true });
    expect(click).toHaveBeenCalledOnce();
  });
});
