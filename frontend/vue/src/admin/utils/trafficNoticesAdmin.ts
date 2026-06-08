import type { PublicNoticeMessage } from '../api/adminRestTrafficNotices';

const SORT_STEP = 10;

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

function todayYmd(): string {
  const now = new Date();
  const y = now.getFullYear();
  const m = String(now.getMonth() + 1).padStart(2, '0');
  const d = String(now.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}
