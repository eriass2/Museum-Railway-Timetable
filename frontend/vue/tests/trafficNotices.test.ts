import { afterEach, describe, expect, it, vi } from 'vitest';
import { fetchTrafficNotices } from '../src/api/trafficNotices';

const config = {
  restUrl: 'https://example.test/wp-json/museum-railway-timetable/v1/',
  restNonce: 'test-nonce',
};

describe('fetchTrafficNotices', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
    vi.restoreAllMocks();
  });

  it('requests traffic-notices with date and day count', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({
        reference_date: '2026-06-06',
        days: 1,
        general: [],
        by_date: [],
        is_empty: true,
      }),
    });
    vi.stubGlobal('fetch', fetchMock);

    const res = await fetchTrafficNotices(config, {
      date: '2026-06-06',
      days: 2,
      showGeneral: true,
      showDeviations: false,
    });

    expect(res.success).toBe(true);
    const [url] = fetchMock.mock.calls[0];
    const parsed = new URL(String(url));
    expect(parsed.pathname).toContain('/traffic-notices');
    expect(parsed.searchParams.get('date')).toBe('2026-06-06');
    expect(parsed.searchParams.get('days')).toBe('2');
    expect(parsed.searchParams.get('show_general')).toBe('1');
    expect(parsed.searchParams.get('show_deviations')).toBe('0');
  });

  it('omits optional query params when not provided', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({
        reference_date: '2026-06-06',
        days: 1,
        general: [],
        by_date: [],
        is_empty: true,
      }),
    });
    vi.stubGlobal('fetch', fetchMock);

    await fetchTrafficNotices(config, {});

    const [url] = fetchMock.mock.calls[0];
    const parsed = new URL(String(url));
    expect(parsed.searchParams.has('date')).toBe(false);
    expect(parsed.searchParams.has('days')).toBe(false);
    expect(parsed.searchParams.has('show_general')).toBe(false);
    expect(parsed.searchParams.has('show_deviations')).toBe(false);
  });
});
