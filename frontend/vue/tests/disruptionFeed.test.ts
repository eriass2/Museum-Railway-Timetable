import { afterEach, describe, expect, it, vi } from 'vitest';
import { fetchDisruptionFeed } from '../src/api/disruptionFeed';

const config = {
  restUrl: 'https://example.test/wp-json/museum-railway-timetable/v1/',
  restNonce: 'test-nonce',
};

describe('fetchDisruptionFeed', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
    vi.restoreAllMocks();
  });

  it('requests traffic-disruptions feed with date and horizon', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({
        reference_date: '2026-06-06',
        horizon_days: 90,
        end_date: '2026-09-04',
        ongoing: [],
        upcoming: [],
        items: [],
        is_empty: true,
      }),
    });
    vi.stubGlobal('fetch', fetchMock);

    const res = await fetchDisruptionFeed(config, {
      date: '2026-06-06',
      horizonDays: 30,
    });

    expect(res.success).toBe(true);
    const [url] = fetchMock.mock.calls[0];
    const parsed = new URL(String(url));
    expect(parsed.pathname).toContain('/traffic-disruptions/feed');
    expect(parsed.searchParams.get('date')).toBe('2026-06-06');
    expect(parsed.searchParams.get('horizon_days')).toBe('30');
  });

  it('omits optional query params when not provided', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({
        reference_date: '2026-06-06',
        horizon_days: 90,
        end_date: '2026-09-04',
        ongoing: [],
        upcoming: [],
        items: [],
        is_empty: true,
      }),
    });
    vi.stubGlobal('fetch', fetchMock);

    await fetchDisruptionFeed(config, {});

    const [url] = fetchMock.mock.calls[0];
    const parsed = new URL(String(url));
    expect(parsed.searchParams.has('date')).toBe(false);
    expect(parsed.searchParams.has('horizon_days')).toBe(false);
  });
});
