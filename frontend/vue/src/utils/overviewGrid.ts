import type { TimetableOverviewRow } from '../types/timetableOverview';

export function isTimeRow(
  row: TimetableOverviewRow,
): row is Extract<TimetableOverviewRow, { cells: { text: string }[] }> {
  return row.kind !== 'trainChange' && row.kind !== 'busConnection';
}

export function isTransferRow(
  row: TimetableOverviewRow,
): row is Extract<TimetableOverviewRow, { kind: 'trainChange' | 'busConnection' }> {
  return row.kind === 'trainChange' || row.kind === 'busConnection';
}

export function overviewRowClass(row: TimetableOverviewRow): string {
  if (row.kind === 'from' || row.kind === 'departure') return 'mrt-ov-grid-row--from';
  if (row.kind === 'to' || row.kind === 'arrival') return 'mrt-ov-grid-row--to';
  if (row.kind === 'trainChange' || row.kind === 'busConnection') return 'mrt-ov-grid-row--transfer';
  return '';
}

export function trainTypeIconUrl(iconUrls: Record<string, string>, key: string): string {
  return iconUrls[key] ?? iconUrls.diesel ?? '';
}
