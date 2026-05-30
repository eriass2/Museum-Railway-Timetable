import { afterEach, describe, expect, it, vi } from 'vitest';
import { mrtPost } from '../src/api/mrtApi';

const config = {
  restUrl: 'https://example.test/wp-json/museum-railway-timetable/v1/',
  restNonce: 'test-nonce',
};

describe('mrtPost (REST)', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('returns data on success', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue({
        ok: true,
        json: async () => ({
          overview: { scope: 'timetable', timetableId: 1, groups: [] },
        }),
      }),
    );

    const res = await mrtPost<{ overview: { timetableId: number } }>(
      config,
      'mrt_timetable_overview_data',
      { timetable_id: 1 },
    );
    expect(res.success).toBe(true);
    expect(res.data?.overview.timetableId).toBe(1);
  });

  it('returns message on HTTP error', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue({
        ok: false,
        status: 500,
        json: async () => ({ message: 'Serverfel' }),
      }),
    );

    const res = await mrtPost(config, 'mrt_timetable_overview_data', { timetable_id: 1 });
    expect(res.success).toBe(false);
    expect(res.message).toBe('Serverfel');
  });

  it('sends REST nonce header on GET overview', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({ overview: {} }),
    });
    vi.stubGlobal('fetch', fetchMock);

    await mrtPost(config, 'mrt_timetable_overview_data', { timetable_id: 42 });
    const [url, init] = fetchMock.mock.calls[0];
    expect(String(url)).toContain('/timetables/42/overview');
    expect(init.method).toBe('GET');
    expect(init.headers['X-WP-Nonce']).toBe('test-nonce');
  });
});
