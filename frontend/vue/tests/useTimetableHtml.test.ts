import { afterEach, describe, expect, it, vi } from 'vitest';
import { loadOverviewHtml } from '../src/composables/useTimetableHtml';

const config = {
  ajaxurl: 'https://example.test/ajax',
  nonce: 'n',
};

describe('loadOverviewHtml', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('returns empty string when timetable id is zero', async () => {
    expect(await loadOverviewHtml(config, 0)).toBe('');
  });

  it('returns html from successful response', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue({
        ok: true,
        json: async () => ({ success: true, data: { html: '<table></table>' } }),
      }),
    );
    expect(await loadOverviewHtml(config, 42)).toBe('<table></table>');
  });
});
