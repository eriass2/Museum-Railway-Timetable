import { adminFetch } from './adminRestCore';

export function devClearDatabase() {
  return adminFetch<{ cleared: boolean }>('/dev/clear-db', { method: 'POST' });
}

export function devImportLennakatten() {
  return adminFetch<{ imported: boolean }>('/dev/import-lennakatten', { method: 'POST' });
}

export function devCreateDemoPage() {
  return adminFetch<{ page_id: number }>('/dev/demo-page', { method: 'POST' });
}

export function devSetupNavigation() {
  return adminFetch<{ menu_id: number; added: number }>('/dev/setup-navigation', {
    method: 'POST',
  });
}

export function devSyncTimetablePages() {
  return adminFetch<{ index_page_id: number; timetable_page_ids: Record<string, number> }>(
    '/dev/sync-timetable-pages',
    { method: 'POST' },
  );
}
