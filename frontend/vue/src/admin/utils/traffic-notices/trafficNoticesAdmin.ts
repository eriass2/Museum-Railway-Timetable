import type { PublicNoticeMessage } from '../../api/adminRestTrafficNotices';
import { todayYmd } from '../../../utils/datetime';

export const TRAFFIC_NOTICE_MAX_LENGTH = 500;

const SORT_STEP = 10;

export type TrafficNoticesViewMode = 'list' | 'edit' | 'create';

export type TrafficNoticeVisibilityKey =
  | 'trafficNoticesInactive'
  | 'trafficNoticesVisibleToday'
  | 'trafficNoticesHiddenToday';

export function sortMessagesByOrder(rows: PublicNoticeMessage[]): PublicNoticeMessage[] {
  return [...rows].sort((a, b) => a.sort_order - b.sort_order);
}

export function messageDraftSnapshot(row: PublicNoticeMessage): string {
  return JSON.stringify(row);
}

export function createNoticeDraft(rows: PublicNoticeMessage[]): PublicNoticeMessage {
  const maxOrder = rows.reduce((max, row) => Math.max(max, row.sort_order), 0);
  return {
    id: `new-${Date.now()}`,
    text: '',
    enabled: true,
    active_from: '',
    active_to: '',
    sort_order: maxOrder + SORT_STEP,
  };
}

export function applyDraftToMessages(
  messages: PublicNoticeMessage[],
  draft: PublicNoticeMessage,
  mode: Exclude<TrafficNoticesViewMode, 'list'>,
): PublicNoticeMessage[] {
  const row = { ...draft, text: draft.text.trim() };
  if (mode === 'create') {
    return [...messages, row];
  }
  return messages.map((item) => (item.id === row.id ? row : item));
}

export function removeMessageById(
  messages: PublicNoticeMessage[],
  id: string,
): PublicNoticeMessage[] {
  return messages.filter((row) => row.id !== id);
}

export function noticeVisibleToday(row: PublicNoticeMessage, today = todayYmd()): boolean {
  if (!row.enabled || !row.text.trim()) {
    return false;
  }
  if (row.active_from && row.active_from > today) {
    return false;
  }
  if (row.active_to && row.active_to < today) {
    return false;
  }
  return true;
}

export function noticeVisibilityLabelKey(
  row: PublicNoticeMessage,
  today?: string,
): TrafficNoticeVisibilityKey {
  if (!row.enabled) {
    return 'trafficNoticesInactive';
  }
  return noticeVisibleToday(row, today)
    ? 'trafficNoticesVisibleToday'
    : 'trafficNoticesHiddenToday';
}

export function reorderMessages(
  rows: PublicNoticeMessage[],
  fromIndex: number,
  toIndex: number,
): PublicNoticeMessage[] {
  const next = [...rows];
  const [moved] = next.splice(fromIndex, 1);
  if (!moved) {
    return rows;
  }
  next.splice(toIndex, 0, moved);
  return renumberSortOrder(next);
}

export function renumberSortOrder(rows: PublicNoticeMessage[]): PublicNoticeMessage[] {
  return rows.map((row, index) => ({
    ...row,
    sort_order: (index + 1) * SORT_STEP,
  }));
}

