import type { TimelineStopWithLabel } from './timelineStop';
import { formatTripClock } from './tripClock';

export function formatTimelineStopTime(stop: TimelineStopWithLabel): string {
  if (stop.time_label) {
    return stop.time_label;
  }
  return formatTripClock(stop.departure_time || stop.arrival_time || '');
}
