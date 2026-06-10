import { afterEach, describe, expect, it, vi } from 'vitest';
import {
  configureMrtLog,
  configureMrtLogFromRestConfig,
  mrtLog,
  resetMrtLogForTests,
  resolveMrtLogSource,
} from '../src/utils/mrtLog';

describe('mrtLog', () => {
  afterEach(() => {
    resetMrtLogForTests();
    vi.unstubAllGlobals();
    vi.restoreAllMocks();
  });

  it('does nothing when dev mode is off', () => {
    const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => undefined);
    mrtLog({ level: 'error', source: 'admin', message: 'hidden' });
    expect(errorSpy).not.toHaveBeenCalled();
  });

  it('writes to console in dev mode', () => {
    configureMrtLog({ isDevMode: true });
    const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => undefined);
    mrtLog({ level: 'error', source: 'wizard', message: 'boom', context: { step: 2 } });
    expect(errorSpy).toHaveBeenCalledWith('[MRT wizard]', 'boom', { step: 2 });
  });

  it('relays admin errors to dev client-log endpoint', async () => {
    const fetchMock = vi.fn().mockResolvedValue({ ok: true });
    vi.stubGlobal('fetch', fetchMock);
    configureMrtLog({
      isDevMode: true,
      relay: {
        restUrl: 'https://example.test/wp-json/museum-railway-timetable/v1/',
        restNonce: 'test-nonce',
      },
    });
    vi.spyOn(console, 'error').mockImplementation(() => undefined);

    mrtLog({ level: 'error', source: 'admin', message: 'REST failed', context: { status: 500 } });

    await vi.waitFor(() => expect(fetchMock).toHaveBeenCalled());
    const [url, init] = fetchMock.mock.calls[0];
    expect(String(url)).toContain('/dev/client-log');
    expect(init.method).toBe('POST');
    expect(init.headers['X-WP-Nonce']).toBe('test-nonce');
    expect(JSON.parse(String(init.body))).toEqual({
      level: 'error',
      source: 'admin',
      message: 'REST failed',
      context: { status: 500 },
    });
  });

  it('derives log source from mount config app id', () => {
    configureMrtLogFromRestConfig({ isDevMode: true, app: 'overview' }, 'month');
    expect(resolveMrtLogSource({ app: 'overview' })).toBe('overview');
    expect(resolveMrtLogSource({})).toBe('month');
  });
});
