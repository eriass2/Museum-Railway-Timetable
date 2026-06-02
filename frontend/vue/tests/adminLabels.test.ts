import { describe, expect, it } from 'vitest';
import { adminStr } from '../src/admin/utils/adminLabels';
import type { AdminClientConfig } from '../src/admin/types';

function cfg(strings: Record<string, string>): AdminClientConfig {
  return {
    restUrl: 'http://example.test/wp-json/museum-railway-timetable/v1/',
    restNonce: 'nonce',
    initialRoute: '/dashboard',
    adminBase: 'http://example.test/wp-admin/admin.php?page=mrt_app',
    canManage: true,
    canOperate: true,
    isDevMode: false,
    trainTypeIconUrls: {},
    strings,
  };
}

describe('adminStr', () => {
  it('reads localized string from config', () => {
    expect(adminStr(cfg({ saved: 'Saved.' }), 'saved')).toBe('Saved.');
  });

  it('falls back when key missing', () => {
    expect(adminStr(cfg({}), 'saved', 'Sparat.')).toBe('Sparat.');
  });
});
