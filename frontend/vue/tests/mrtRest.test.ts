import { afterEach, describe, expect, it, vi } from 'vitest';
import { mrtRestRequest } from '../src/api/mrtRest';
import { configureMrtLog, resetMrtLogForTests } from '../src/utils/mrtLog';

const config = {
  restUrl: 'https://example.test/wp-json/museum-railway-timetable/v1/',
  restNonce: 'test-nonce',
};

describe('mrtRestRequest', () => {
  afterEach(() => {
    resetMrtLogForTests();
    vi.unstubAllGlobals();
    vi.restoreAllMocks();
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

    const res = await mrtRestRequest<{ overview: { timetableId: number } }>(config, {
      method: 'GET',
      path: 'timetables/1/overview',
    });
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
    const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => undefined);
    configureMrtLog({ isDevMode: true, defaultSource: 'wizard' });

    const res = await mrtRestRequest(
      { ...config, isDevMode: true, app: 'wizard' },
      {
        method: 'GET',
        path: 'timetables/1/overview',
      },
    );
    expect(res.success).toBe(false);
    expect(res.message).toBe('Serverfel');
    expect(errorSpy).toHaveBeenCalledWith(
      '[MRT wizard]',
      'REST GET timetables/1/overview → 500',
      { response: { message: 'Serverfel' } },
    );
  });

  it('includes journey legs in trip prices query string', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({ zones: 2, trip: null, day: null }),
    });
    vi.stubGlobal('fetch', fetchMock);

    const legs = JSON.stringify([
      { service_id: 10, from_station_id: 1, to_station_id: 5 },
    ]);
    await mrtRestRequest(config, {
      method: 'GET',
      path: 'prices/trip',
      query: {
        from_id: 1,
        to_id: 5,
        trip_type: 'single',
        outbound_legs: legs,
        inbound_legs: '',
      },
    });

    const [url] = fetchMock.mock.calls[0];
    const parsed = new URL(String(url));
    expect(parsed.pathname).toContain('/prices/trip');
    expect(parsed.searchParams.get('from_id')).toBe('1');
    expect(parsed.searchParams.get('to_id')).toBe('5');
    expect(parsed.searchParams.get('outbound_legs')).toBe(legs);
    expect(parsed.searchParams.has('inbound_legs')).toBe(false);
  });

  it('sends REST nonce header on GET overview', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({ overview: {} }),
    });
    vi.stubGlobal('fetch', fetchMock);

    await mrtRestRequest(config, {
      method: 'GET',
      path: 'timetables/42/overview',
    });
    const [url, init] = fetchMock.mock.calls[0];
    expect(String(url)).toContain('/timetables/42/overview');
    expect(init.method).toBe('GET');
    expect(init.headers['X-WP-Nonce']).toBe('test-nonce');
  });

  it('sends JSON body on POST journey search', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({ connections: [] }),
    });
    vi.stubGlobal('fetch', fetchMock);

    await mrtRestRequest(config, {
      method: 'POST',
      path: 'journey/search',
      body: { from_station: 1, to_station: 2, date: '2026-05-01' },
    });

    const [url, init] = fetchMock.mock.calls[0];
    expect(String(url)).toContain('/journey/search');
    expect(init.method).toBe('POST');
    expect(init.headers['Content-Type']).toBe('application/json');
    expect(JSON.parse(String(init.body))).toEqual({
      from_station: 1,
      to_station: 2,
      date: '2026-05-01',
    });
  });
});
