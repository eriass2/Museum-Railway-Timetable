import { afterEach, describe, expect, it, vi } from 'vitest';
import { quickDeparture } from '../src/admin/api/adminRest';

describe('quickDeparture REST', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
    delete (window as { mrtAdminVue?: unknown }).mrtAdminVue;
  });

  it('sends departure to quick-edit endpoint', async () => {
    window.mrtAdminVue = {
      restUrl: 'https://example.test/wp-json/museum-railway-timetable/v1/',
      restNonce: 'nonce',
      initialRoute: '/dashboard',
      adminBase: 'https://example.test/wp-admin/admin.php?page=mrt_app',
      canManage: false,
      canOperate: true,
      isDevMode: false,
    };

    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({ saved: true }),
    });
    vi.stubGlobal('fetch', fetchMock);

    const res = await quickDeparture(42, '09:15');
    expect(res.saved).toBe(true);
    expect(fetchMock).toHaveBeenCalledWith(
      'https://example.test/wp-json/museum-railway-timetable/v1/services/42/departure',
      expect.objectContaining({ method: 'PUT' }),
    );
  });
});
