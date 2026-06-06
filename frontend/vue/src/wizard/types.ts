export type WizardStep = 'route' | 'date' | 'outbound' | 'return' | 'summary';

export type TripType = 'single' | 'return';

import type { CalendarDayInfo, CalendarDayStatus } from '../shared/calendarDay';
export type { CalendarDayInfo, CalendarDayStatus };

export type { JourneyLeg, JourneyConnection } from '../shared/journey';

export type TimelineStop = {
  station_title?: string;
  departure_time?: string;
  arrival_time?: string;
};

export type ConnectionDetailPayload = {
  detail?: { stops?: TimelineStop[] };
  notice?: string;
  is_cancelled?: boolean;
};
