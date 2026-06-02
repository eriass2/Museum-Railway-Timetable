import { describe, expect, it } from 'vitest';
import { adminErrorMessage, adminFmt, adminFmtN, adminStr } from '../src/admin/utils/adminLabels';
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

describe('adminFmt', () => {
  it('replaces %s placeholders in order', () => {
    expect(adminFmt(cfg({ msg: 'Hello %s!' }), 'msg', 'world')).toBe('Hello world!');
  });
});

describe('adminFmtN', () => {
  it('replaces numbered placeholders', () => {
    const strings = { summary: '%1$s stationer · %2$s rutter' };
    expect(adminFmtN(cfg(strings), 'summary', { 1: 3, 2: 5 })).toBe('3 stationer · 5 rutter');
  });
});

describe('adminErrorMessage', () => {
  it('returns Error message when present', () => {
    expect(adminErrorMessage(cfg({}), new Error('boom'), 'loadFailed', 'Fail')).toBe('boom');
  });

  it('falls back to localized string', () => {
    expect(adminErrorMessage(cfg({ loadFailed: 'Load failed.' }), 'x', 'loadFailed')).toBe(
      'Load failed.',
    );
  });
});
