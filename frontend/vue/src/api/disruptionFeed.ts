import type { MrtRestConfig } from '../config/types';
import { mrtRestRequest } from './mrtRest';

export type DisruptionFeedKind = 'cancelled' | 'deviation' | 'info';

export type DisruptionFeedItem = {
  id: string;
  source: 'general' | 'deviation';
  kind: DisruptionFeedKind;
  phase: 'ongoing' | 'upcoming' | 'past';
  date_from: string;
  date_to: string;
  date_label: string;
  headline: string;
  body: string;
  train_numbers: string[];
  service_ids: number[];
  edit?: {
    path: string;
    label: string;
    query?: Record<string, string>;
  };
};

export type DisruptionFeedPayload = {
  reference_date: string;
  horizon_days: number;
  end_date: string;
  ongoing: DisruptionFeedItem[];
  upcoming: DisruptionFeedItem[];
  items: DisruptionFeedItem[];
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
