export type WizardStep = 'route' | 'date' | 'outbound' | 'return' | 'summary';

export type TripType = 'single' | 'return';

export type CalendarDayStatus = 'ok' | 'traffic_no_match' | 'none';

export type JourneyLeg = {
  service_id: number;
  service_name?: string;
  service_number?: string;
  train_type?: string;
  train_type_slug?: string;
  train_type_icon?: string;
  from_station_id?: number;
  to_station_id?: number;
  from_departure?: string;
  to_arrival?: string;
  destination?: string;
  direction?: string;
  duration_minutes?: number;
};

export type JourneyConnection = {
  service_id: number;
  service_name?: string;
  train_type?: string;
  train_type_slug?: string;
  train_type_icon?: string;
  from_departure?: string;
  from_arrival?: string;
  to_arrival?: string;
  to_departure?: string;
  departure?: string;
  arrival?: string;
  duration_minutes?: number;
  connection_type?: string;
  notice?: string;
  legs?: JourneyLeg[];
  transfer_wait_minutes?: number;
};

export type TimelineStop = {
  station_title?: string;
  departure_time?: string;
  arrival_time?: string;
};

export type ConnectionDetailPayload = {
  detail?: { stops?: TimelineStop[] };
  notice?: string;
};
