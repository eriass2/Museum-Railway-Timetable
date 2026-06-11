import type {
  TimetableOverviewColumn,
  TimetableOverviewRow,
} from '../../../types/timetableOverview';

export function isOverviewTimeCellEditable(
  readonly: boolean | undefined,
  row: TimetableOverviewRow,
  column: TimetableOverviewColumn,
): boolean {
  if (readonly) {
    return false;
  }
  if (!('stationId' in row) || !row.stationId || !column.serviceId) {
    return false;
  }
  return true;
}
