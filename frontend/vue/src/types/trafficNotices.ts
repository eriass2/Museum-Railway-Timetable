export type TrafficNoticesLabels = {
  empty: string;
  loading: string;
  error: string;
  deviationPrefix: string;
};

export type TrafficNoticesVueConfig = {
  app: 'traffic_notices';
  referenceDate?: string;
  days?: number;
  showGeneral?: boolean;
  showDeviations?: boolean;
  title?: string;
  labels?: TrafficNoticesLabels;
};
