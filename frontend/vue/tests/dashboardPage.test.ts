import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { getDashboard } from '../src/admin/api/adminRest';
import type { AdminClientConfig } from '../src/admin/types';

const dashboardPayload = {
  stats: {
    stations: 3,
    routes: 2,
    timetables: 1,
    services: 4,
    train_types: 2,
  },
  warnings: [{ code: 'test', message: 'Testvarning', route: '#/timetables/1' }],
  next_traffic: [],
  links: { front: 'https://example.test/' },
  can_manage: true,
  can_operate: true,
};

import { buildAdminRestUrl } from '../src/admin/api/adminRest';

describe('buildAdminRestUrl', () => {
  it('joins trailing-slash rest base with path', () => {
    expect(
      buildAdminRestUrl('https://example.test/wp-json/museum-railway-timetable/v1/', '/timetables'),
    ).toBe('https://example.test/wp-json/museum-railway-timetable/v1/timetables');
  });
});

describe('admin REST dashboard', () => {
  const adminWindow: { mrtAdminVue?: AdminClientConfig } = {};

  beforeEach(() => {
    vi.stubGlobal('window', adminWindow);
  });

  afterEach(() => {
    delete adminWindow.mrtAdminVue;
    vi.unstubAllGlobals();
  });

  it('loads dashboard payload with REST nonce', async () => {
    adminWindow.mrtAdminVue = {
      restUrl: 'https://example.test/wp-json/museum-railway-timetable/v1/',
      restNonce: 'test-nonce',
      initialRoute: '/dashboard',
      adminBase: 'https://example.test/wp-admin/admin.php?page=mrt_app',
      canManage: true,
      canOperate: true,
      isDevMode: false,
      trainTypeIconUrls: {},
    };

    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => dashboardPayload,
    });
    vi.stubGlobal('fetch', fetchMock);

    const data = await getDashboard();
    expect(data.stats.stations).toBe(3);
    expect(data.warnings[0]?.message).toBe('Testvarning');
    expect(fetchMock).toHaveBeenCalledWith(
      'https://example.test/wp-json/museum-railway-timetable/v1/dashboard',
      expect.objectContaining({
        headers: expect.any(Headers),
      }),
    );
    const headers = fetchMock.mock.calls[0]?.[1]?.headers as Headers;
    expect(headers.get('X-WP-Nonce')).toBe('test-nonce');
  });
});
