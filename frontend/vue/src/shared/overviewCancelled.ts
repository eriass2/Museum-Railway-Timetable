import type { TimetableOverviewColumn } from '../types/timetableOverview';
import { isCancelledNotice } from './cancelledNotice';

export function overviewColumnIsCancelled(column: TimetableOverviewColumn): boolean {
  return column.isCancelled === true || isCancelledNotice(column.deviationNotice || '');
}
