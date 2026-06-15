/** Passenger-facing behovsuppehåll hint from journey connection-detail API. */
export type BehovHint = '' | 'pickup' | 'dropoff' | 'both';

/** Shared stop shape for timeline rendering and footnotes. */
export type TimelineStopBase = {
  station_title?: string;
  departure_time?: string;
  arrival_time?: string;
  approximate_time?: boolean;
  behov_hint?: BehovHint;
};

/** Wizard/API stop with optional pre-formatted clock label. */
export type TimelineStopWithLabel = TimelineStopBase & {
  time_label?: string;
};
