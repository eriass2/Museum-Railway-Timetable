import type { TimetableBranchTrip } from '../types/timetableOverview';
import { isCancelledNotice } from './cancelledNotice';

export function branchTripIsCancelled(trip: TimetableBranchTrip): boolean {
  return trip.isCancelled === true || isCancelledNotice(trip.deviationNotice || '');
}

export function branchTripNoticeDetail(trip: TimetableBranchTrip, cancelledLabel: string): string {
  if (!branchTripIsCancelled(trip)) {
    return trip.deviationNotice?.trim() || '';
  }
  const notice = trip.deviationNotice?.trim() || '';
  if (!notice || notice.toLowerCase() === cancelledLabel.toLowerCase()) {
    return '';
  }
  return notice;
}
