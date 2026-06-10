import { afterEach, describe, expect, it, vi } from 'vitest';
import { submitWizardFeedback } from '../src/api/wizardFeedback';

const config = {
  restUrl: 'https://example.test/wp-json/museum-railway-timetable/v1/',
  restNonce: 'test-nonce',
};

describe('submitWizardFeedback', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('posts feedback payload to wizard feedback endpoint', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({ saved: true, id: 123 }),
    });
    vi.stubGlobal('fetch', fetchMock);

    const response = await submitWizardFeedback(config, {
      type: 'bug',
      message: 'Datumsteget visar fel månad.',
      email: 'test@example.com',
      pageUrl: 'https://example.test/wizard',
      wizardStep: 'date',
      context: { tripType: 'return' },
    });

    const [url, init] = fetchMock.mock.calls[0];
    expect(response.data?.id).toBe(123);
    expect(String(url)).toContain('/wizard/feedback');
    expect(init.method).toBe('POST');
    expect(init.headers['X-WP-Nonce']).toBe('test-nonce');
    expect(JSON.parse(String(init.body))).toMatchObject({
      message: 'Datumsteget visar fel månad.',
      wizardStep: 'date',
    });
  });
});
