import type { MrtRestConfig } from '../config/types';
import { mrtRestRequest } from './mrtRest';

export type TrafficNoticeGeneral = {
  id: string;
  text: string;
};

export type TrafficNoticeDeviation = {
  service_id: number;
  service_number: string;
  route_label: string;
  trip_label: string;
  notice: string;
  is_cancelled: boolean;
  train_type_id: number;
};

export type TrafficNoticeDateGroup = {
  date: string;
  date_label: string;
  deviations: TrafficNoticeDeviation[];
};

export type TrafficNoticesPayload = {
  reference_date: string;
  days: number;
  general: TrafficNoticeGeneral[];
  by_date: TrafficNoticeDateGroup[];
  is_empty: boolean;
};

export async function fetchTrafficNotices(
  config: MrtRestConfig,
  query: {
    date?: string;
    days?: number;
    showGeneral?: boolean;
    showDeviations?: boolean;
  },
) {
  const q: Record<string, string | number> = {};
  if (query.date) {
    q.date = query.date;
  }
  if (query.days !== undefined) {
    q.days = query.days;
  }
  if (query.showGeneral !== undefined) {
    q.show_general = query.showGeneral ? 1 : 0;
  }
  if (query.showDeviations !== undefined) {
    q.show_deviations = query.showDeviations ? 1 : 0;
  }
  return mrtRestRequest<TrafficNoticesPayload>(config, {
    method: 'GET',
    path: 'traffic-notices',
    query: q,
  });
}
