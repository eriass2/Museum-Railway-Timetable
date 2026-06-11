import type { DisruptionFeedPayload } from '../../api/disruptionFeed';
import { adminFetch } from './adminRestCore';

export type PublicNoticeMessage = {
  id: string;
  text: string;
  enabled: boolean;
  active_from: string;
  active_to: string;
  sort_order: number;
};

export function listTrafficNoticeMessages() {
  return adminFetch<{ messages: PublicNoticeMessage[] }>('traffic-notices/messages');
}

export function saveTrafficNoticeMessages(messages: PublicNoticeMessage[]) {
  return adminFetch<{ saved: boolean; messages: PublicNoticeMessage[] }>(
    'traffic-notices/messages',
    {
      method: 'PUT',
      body: JSON.stringify({ messages }),
    },
  );
}

export function fetchTrafficNoticesFeedPreview(horizonDays = 90) {
  return adminFetch<DisruptionFeedPayload>(
    'traffic-notices/feed',
    {},
    { horizon_days: horizonDays },
  );
}
