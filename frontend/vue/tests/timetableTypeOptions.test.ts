import { describe, expect, it } from 'vitest';
import { buildTimetableTypeOptions } from '../src/admin/utils/timetableTypeOptions';
import type { AdminClientConfig } from '../src/admin/types';

function cfg(strings: Record<string, string>): AdminClientConfig {
  return {
    restUrl: 'http://example.test/wp-json/museum-railway-timetable/v1/',
    restNonce: 'nonce',
    initialRoute: '/timetables',
    adminBase: 'http://example.test/wp-admin/admin.php?page=mrt_app',
    canManage: true,
    canOperate: true,
    isDevMode: false,
    trainTypeIconUrls: {},
    strings,
  };
}

describe('buildTimetableTypeOptions', () => {
  it('returns five timetable type options with translated labels', () => {
    const options = buildTimetableTypeOptions(
      cfg({
        editorTypeNone: 'Ingen',
        editorTypeGreen: 'Grön',
        editorTypeYellow: 'Gul',
        editorTypeRed: 'Röd',
        editorTypeOrange: 'Orange',
      }),
    );
    expect(options).toHaveLength(5);
    expect(options.map((opt) => opt.value)).toEqual(['', 'green', 'yellow', 'red', 'orange']);
    expect(options[1]?.label).toBe('Grön');
  });
});
