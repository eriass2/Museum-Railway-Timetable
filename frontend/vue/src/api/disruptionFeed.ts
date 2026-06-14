import type { MrtRestConfig } from '../config/types';
import { mrtRestRequest } from './mrtRest';

export type DisruptionFeedKind = 'cancelled' | 'deviation' | 'info';

export type DisruptionFeedSeverity = 'info' | 'warning';

export type DisruptionFeedDetailSection = {
  title: string;
  lines: string[];
};

export type DisruptionFeedItem = {
  id: string;
  source: 'general' | 'deviation';
  kind: DisruptionFeedKind;
  phase: 'ongoing' | 'upcoming' | 'past';
  date_from: string;
  date_to: string;
  date_label: string;
  headline: string;
  summary?: string;
  validity_label?: string;
  line_label?: string;
  severity?: DisruptionFeedSeverity;
  category_key?: string;
  category_label?: string;
  icon_key?: string;
  body: string;
  route_label: string;
  detail_intro: string;
  detail_sections: DisruptionFeedDetailSection[];
  train_numbers: string[];
  service_ids: number[];
  edit?: {
    path: string;
    label: string;
    query?: Record<string, string>;
  };
};

export type DisruptionFeedCategory = {
  key: string;
  label: string;
  icon_key: string;
  counts: { info: number; warning: number };
  items: DisruptionFeedItem[];
};

export type DisruptionFeedPanel = {
  key: 'ongoing' | 'upcoming';
  title: string;
  icon: 'clock' | 'calendar';
  categories: DisruptionFeedCategory[];
};

export type DisruptionFeedPayload = {
  reference_date: string;
  horizon_days: number;
  end_date: string;
  ongoing: DisruptionFeedItem[];
  upcoming: DisruptionFeedItem[];
  items: DisruptionFeedItem[];
  panels?: DisruptionFeedPanel[];
  is_empty: boolean;
};

export async function fetchDisruptionFeed(
  config: MrtRestConfig,
  query: {
    date?: string;
    horizonDays?: number;
  },
) {
  const q: Record<string, string | number> = {};
  if (query.date) {
    q.date = query.date;
  }
  if (query.horizonDays !== undefined) {
    q.horizon_days = query.horizonDays;
  }
  return mrtRestRequest<DisruptionFeedPayload>(config, {
    method: 'GET',
    path: 'traffic-disruptions/feed',
    query: q,
  });
}
