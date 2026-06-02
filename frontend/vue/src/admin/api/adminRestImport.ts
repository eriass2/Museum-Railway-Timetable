import { adminFetch, adminUpload } from './adminRestCore';

export function exportCsv(options: { include_prices?: boolean; include_settings?: boolean }) {
  const query: Record<string, string | number> = {};
  if (options.include_prices !== undefined) {
    query.include_prices = options.include_prices ? '1' : '0';
  }
  if (options.include_settings !== undefined) {
    query.include_settings = options.include_settings ? '1' : '0';
  }
  return adminFetch<{ filename: string; content_base64: string }>(
    'export/csv',
    {},
    Object.keys(query).length ? query : undefined,
  );
}

export function importCsv(file: File, mode: 'merge' | 'override') {
  const body = new FormData();
  body.append('file', file);
  body.append('mode', mode);
  return adminUpload<{ imported: boolean; stats: Record<string, number>; mode: string }>(
    'import/csv',
    body,
  );
}
