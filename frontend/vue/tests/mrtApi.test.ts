import { afterEach, describe, expect, it, vi } from 'vitest';
import { mrtPost } from '../src/api/mrtApi';

const config = {
  ajaxurl: 'https://example.test/wp-admin/admin-ajax.php',
  nonce: 'test-nonce',
};

describe('mrtPost', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('returns data on success', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue({
        ok: true,
        json: async () => ({ success: true, data: { html: '<p>ok</p>' } }),
      }),
    );

    const res = await mrtPost<{ html: string }>(config, 'mrt_timetable_overview_html', {
      timetable_id: 1,
    });
    expect(res.success).toBe(true);
    expect(res.data?.html).toBe('<p>ok</p>');
  });

  it('returns message on HTTP error', async () => {
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue({ ok: false }));

    const res = await mrtPost(config, 'mrt_test');
    expect(res.success).toBe(false);
    expect(res.message).toBe('Nätverksfel');
  });

  it('includes nonce in POST body', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({ success: true, data: {} }),
    });
    vi.stubGlobal('fetch', fetchMock);

    await mrtPost(config, 'mrt_test', { foo: 1 });
    const body = String(fetchMock.mock.calls[0][1].body);
    expect(body).toContain('nonce=test-nonce');
    expect(body).toContain('action=mrt_test');
  });
});
