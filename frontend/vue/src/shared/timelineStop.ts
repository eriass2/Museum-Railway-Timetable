/** Shared stop shape for timeline rendering and footnotes. */
export type TimelineStopBase = {
  station_title?: string;
  departure_time?: string;
  arrival_time?: string;
  approximate_time?: boolean;
  on_request_pickup?: boolean;
  on_request_dropoff?: boolean;
  on_request_both?: boolean;
};

/** Wizard/API stop with optional pre-formatted clock label. */
export type TimelineStopWithLabel = TimelineStopBase & {
  time_label?: string;
};
