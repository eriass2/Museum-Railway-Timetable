import { overviewColumnIsCancelled } from '../shared/overviewCancelled';
import type { OverviewUiLabels } from '../shared/overviewUiLabels';
import { formatDeviationPlanned } from '../shared/overviewUiLabels';
import type { TimetableOverviewColumn, TimetableRailGroup } from '../types/timetableOverview';

export function overviewColumnAt(
  group: TimetableRailGroup,
  index: number,
): TimetableOverviewColumn | undefined {
  return group.columns[index];
}

export function overviewColumnCancelled(group: TimetableRailGroup, index: number): boolean {
  const column = overviewColumnAt(group, index);
  return column ? overviewColumnIsCancelled(column) : false;
}

export function overviewCancelledNoticeDetail(
  group: TimetableRailGroup,
  index: number,
  cancelledLabel: string,
): boolean {
  const column = overviewColumnAt(group, index);
  if (!column || !overviewColumnIsCancelled(column)) {
    return false;
  }
  const notice = column.deviationNotice?.trim() || '';
  if (!notice) {
    return false;
  }
  return notice.toLowerCase() !== cancelledLabel.toLowerCase();
}

export function overviewDeviationTitle(
  labels: OverviewUiLabels,
  plannedName: string | undefined,
): string {
  if (plannedName) {
    return formatDeviationPlanned(labels.deviationPlanned, plannedName);
  }
  return labels.deviationFromPlan;
}
