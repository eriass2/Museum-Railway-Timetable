export type WizardStep = 'route' | 'date' | 'outbound' | 'return' | 'summary';

export type TripType = 'single' | 'return';

import type { CalendarDayInfo, CalendarDayStatus } from '../shared/calendarDay';
import type { TimelineStopWithLabel } from '../shared/timelineStop';
export type { CalendarDayInfo, CalendarDayStatus };

export type { JourneyLeg, JourneyConnection } from '../shared/journey';

export type { TimelineStopWithLabel as TimelineStop } from '../shared/timelineStop';

export type ConnectionDetailPayload = {
  detail?: { stops?: TimelineStopWithLabel[] };
  notice?: string;
  is_cancelled?: boolean;
};
