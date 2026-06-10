import { describe, expect, it, vi, afterEach } from 'vitest';
import { syncDayCalendarQuery, syncMonthCalendarQuery } from '../src/utils/monthCalendarQuery';

describe('syncMonthCalendarQuery', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('updates mrt_month query param via replaceState', () => {
    const replaceState = vi.fn();
    vi.stubGlobal('window', {
      location: new URL('https://example.com/tidtabell?foo=1'),
      history: { replaceState },
    });

    syncMonthCalendarQuery(2026, 6);

    expect(replaceState).toHaveBeenCalledOnce();
    const url = replaceState.mock.calls[0][2] as string;
    expect(url).toContain('mrt_month=2026-06');
    expect(url).toContain('foo=1');
  });

  it('updates mrt_date and mrt_month when a day is selected', () => {
    const replaceState = vi.fn();
    vi.stubGlobal('window', {
      location: new URL('https://example.com/tidtabeller?foo=1'),
      history: { replaceState },
    });

    syncDayCalendarQuery('2026-06-14');

    expect(replaceState).toHaveBeenCalledOnce();
    const url = replaceState.mock.calls[0][2] as string;
    expect(url).toContain('mrt_date=2026-06-14');
    expect(url).toContain('mrt_month=2026-06');
  });

  it('removes mrt_date when day panel closes', () => {
    const replaceState = vi.fn();
    vi.stubGlobal('window', {
      location: new URL('https://example.com/tidtabeller?mrt_date=2026-06-14&mrt_month=2026-06'),
      history: { replaceState },
    });

    syncDayCalendarQuery(null);

    const url = replaceState.mock.calls[0][2] as string;
    expect(url).not.toContain('mrt_date=');
    expect(url).toContain('mrt_month=2026-06');
  });
});
