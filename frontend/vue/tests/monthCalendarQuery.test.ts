import { describe, expect, it, vi, afterEach } from 'vitest';
import { syncMonthCalendarQuery } from '../src/utils/monthCalendarQuery';

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
});
