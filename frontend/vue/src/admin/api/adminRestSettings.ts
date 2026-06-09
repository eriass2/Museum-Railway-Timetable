import { adminFetch } from './adminRestCore';

export type SettingsPayload = {
  enabled: boolean;
  note: string;
  operator_name: string;
  ticket_url: string;
  hero_background_url: string;
  min_transfer_minutes: number;
  max_transfer_minutes: number;
  max_transfers: number;
  afternoon_return_threshold_minutes: number;
};

export function getSettings() {
  return adminFetch<SettingsPayload>('/settings');
}

export function saveSettings(body: SettingsPayload) {
  return adminFetch<SettingsPayload>('/settings', {
    method: 'PATCH',
    body: JSON.stringify(body),
  });
}

export type PricesPayload = {
  matrix: Record<string, Record<string, Record<number, number | null>>>;
  ticket_types: Record<string, string>;
  categories: Record<string, string>;
  zones: number[];
  zone_cap: number;
  afternoon_return: Record<string, number | null>;
};

export function getPrices() {
  return adminFetch<PricesPayload>('/settings/prices');
}

export function savePrices(
  payload: Pick<
    PricesPayload,
    'matrix' | 'ticket_types' | 'categories' | 'zones' | 'zone_cap' | 'afternoon_return'
  >,
) {
  return adminFetch<PricesPayload>('/settings/prices', {
    method: 'PATCH',
    body: JSON.stringify(payload),
  });
}

export function listTrainTypes() {
  return adminFetch<{ items: import('../types').TrainTypeRow[]; icon_keys: string[] }>(
    '/train-types',
  );
}

export function createTrainType(body: { name: string; slug?: string; icon_key?: string }) {
  return adminFetch<import('../types').TrainTypeRow>('/train-types', {
    method: 'POST',
    body: JSON.stringify(body),
  });
}

export function updateTrainType(
  id: number,
  body: Partial<{ name: string; slug: string; icon_key: string }>,
) {
  return adminFetch<import('../types').TrainTypeRow>(`/train-types/${id}`, {
    method: 'PATCH',
    body: JSON.stringify(body),
  });
}

export function deleteTrainType(id: number) {
  return adminFetch<{ deleted: boolean }>(`/train-types/${id}`, { method: 'DELETE' });
}
