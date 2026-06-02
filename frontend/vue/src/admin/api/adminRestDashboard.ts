import { adminFetch } from './adminRestCore';

export function getDashboard() {
  return adminFetch<import('../types').DashboardPayload>('/dashboard');
}

export function cancelTrafficToday(date?: string, notice?: string) {
  const body: { date?: string; notice?: string } = {};
  if (date) {
    body.date = date;
  }
  if (notice) {
    body.notice = notice;
  }
  return adminFetch<{ date: string; notice: string; services_updated: number }>(
    '/operations/cancel-traffic',
    {
      method: 'POST',
      body: JSON.stringify(body),
    },
  );
}
