export type TrafficNoticesLabels = {
  empty: string;
  loading: string;
  error: string;
  sectionOngoing: string;
  sectionUpcoming: string;
};

export type TrafficNoticesVueConfig = {
  app: 'traffic_notices';
  referenceDate?: string;
  horizonDays?: number;
  title?: string;
  labels?: TrafficNoticesLabels;
};
