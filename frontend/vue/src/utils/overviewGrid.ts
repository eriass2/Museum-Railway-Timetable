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

export function overviewRowClass(row: TimetableOverviewRow, rowIndex = 0): string {
  const classes: string[] = [];
  if (row.kind === 'from' || row.kind === 'departure') {
    classes.push('mrt-ov-grid-row--from');
  } else if (row.kind === 'to' || row.kind === 'arrival') {
    classes.push('mrt-ov-grid-row--to');
  } else if (row.kind === 'trainChange' || row.kind === 'busConnection') {
    classes.push('mrt-ov-grid-row--transfer');
  } else if (row.kind === 'station' && rowIndex % 2 === 0) {
    classes.push('mrt-ov-grid-row--alt');
  }
  return classes.join(' ');
}

export function trainTypeIconUrl(iconUrls: Record<string, string>, key: string): string {
  return iconUrls[key] ?? iconUrls.diesel ?? '';
}
