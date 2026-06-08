import { describe, expect, it } from 'vitest';
import {
  noticeVisibleToday,
  reorderMessages,
  renumberSortOrder,
} from '../src/admin/utils/trafficNoticesAdmin';
import type { PublicNoticeMessage } from '../src/admin/api/adminRestTrafficNotices';

function row(overrides: Partial<PublicNoticeMessage> = {}): PublicNoticeMessage {
  return {
    id: '1',
    text: 'Test',
    enabled: true,
    active_from: '',
    active_to: '',
    sort_order: 10,
    ...overrides,
  };
}

describe('noticeVisibleToday', () => {
  it('returns false when disabled or empty', () => {
    expect(noticeVisibleToday(row({ enabled: false }), '2026-06-06')).toBe(false);
    expect(noticeVisibleToday(row({ text: '   ' }), '2026-06-06')).toBe(false);
  });

  it('respects active_from and active_to', () => {
    const bounded = row({ active_from: '2026-06-06', active_to: '2026-06-07' });
    expect(noticeVisibleToday(bounded, '2026-06-05')).toBe(false);
    expect(noticeVisibleToday(bounded, '2026-06-06')).toBe(true);
    expect(noticeVisibleToday(bounded, '2026-06-08')).toBe(false);
  });
});

describe('reorderMessages', () => {
  it('moves a row and renumbers sort_order', () => {
    const rows = [row({ id: 'a', sort_order: 10 }), row({ id: 'b', sort_order: 20 })];
    const next = reorderMessages(rows, 0, 1);
    expect(next.map((r) => r.id)).toEqual(['b', 'a']);
    expect(next.map((r) => r.sort_order)).toEqual([10, 20]);
  });
});

describe('renumberSortOrder', () => {
  it('assigns gaps of 10', () => {
    const rows = [row({ sort_order: 99 }), row({ sort_order: 1 })];
    expect(renumberSortOrder(rows).map((r) => r.sort_order)).toEqual([10, 20]);
  });
});
