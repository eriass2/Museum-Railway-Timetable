import type { DisruptionFeedPanel, DisruptionFeedPayload } from '@/api/disruptionFeed';

/** Panels are built server-side; client passes through API `panels[]`. */
export function resolveDisruptionPanels(payload: DisruptionFeedPayload): DisruptionFeedPanel[] {
  return payload.panels ?? [];
}
