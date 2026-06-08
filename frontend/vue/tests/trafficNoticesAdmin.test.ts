import { describe, expect, it } from 'vitest';
import {
  applyDraftToMessages,
  createNoticeDraft,
  noticeVisibilityLabelKey,
  noticeVisibleToday,
  removeMessageById,
  reorderMessages,
  renumberSortOrder,
  sortMessagesByOrder,
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

describe('noticeVisibilityLabelKey', () => {
  it('maps enabled and date bounds to label keys', () => {
    expect(noticeVisibilityLabelKey(row({ enabled: false }))).toBe('trafficNoticesInactive');
    expect(noticeVisibilityLabelKey(row({ active_from: '2099-01-01' }), '2026-06-06')).toBe(
      'trafficNoticesHiddenToday',
    );
  });
});

describe('sortMessagesByOrder', () => {
  it('sorts by sort_order ascending', () => {
    const sorted = sortMessagesByOrder([
      row({ id: 'b', sort_order: 20 }),
      row({ id: 'a', sort_order: 10 }),
    ]);
    expect(sorted.map((item) => item.id)).toEqual(['a', 'b']);
  });
});

describe('applyDraftToMessages', () => {
  it('appends on create and replaces on edit', () => {
    const existing = [row({ id: 'a', text: 'Old' })];
    const created = applyDraftToMessages(existing, row({ id: 'b', text: 'New' }), 'create');
    expect(created).toHaveLength(2);
    const edited = applyDraftToMessages(existing, row({ id: 'a', text: 'Updated' }), 'edit');
    expect(edited[0]?.text).toBe('Updated');
  });
});

describe('createNoticeDraft', () => {
  it('assigns sort_order above existing rows', () => {
    const draft = createNoticeDraft([row({ sort_order: 30 })]);
    expect(draft.sort_order).toBe(40);
    expect(draft.text).toBe('');
  });
});

describe('removeMessageById', () => {
  it('drops the matching row', () => {
    const next = removeMessageById([row({ id: 'a' }), row({ id: 'b' })], 'a');
    expect(next.map((item) => item.id)).toEqual(['b']);
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
