import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { quickDeparture } from '../src/admin/api/adminRest';
import type { AdminClientConfig } from '../src/admin/types';

describe('quickDeparture REST', () => {
  const adminWindow: { mrtAdminVue?: AdminClientConfig } = {};

  beforeEach(() => {
    vi.stubGlobal('window', adminWindow);
  });

  afterEach(() => {
    delete adminWindow.mrtAdminVue;
    vi.unstubAllGlobals();
  });

  it('sends departure to quick-edit endpoint', async () => {
    adminWindow.mrtAdminVue = {
      restUrl: 'https://example.test/wp-json/museum-railway-timetable/v1',
      restNonce: 'nonce',
      initialRoute: '/dashboard',
      adminBase: 'https://example.test/wp-admin/admin.php?page=mrt_app',
      canManage: false,
      canOperate: true,
      isDevMode: false,
      trainTypeIconUrls: {},
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
