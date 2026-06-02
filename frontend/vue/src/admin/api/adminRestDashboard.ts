import { adminFetch } from './adminRestCore';

export function getDashboard() {
  return adminFetch<import('../types').DashboardPayload>('/dashboard');
}

export function cancelTrafficToday(date?: string, notice = 'Inställd') {
  return adminFetch<{ date: string; notice: string; services_updated: number }>(
    '/operations/cancel-traffic',
    {
      method: 'POST',
      body: JSON.stringify({ date, notice }),
    },
  );
}
