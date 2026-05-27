import type { MrtAjaxConfig } from '../config/types';

export type MrtAjaxResponse<T> = {
  success: boolean;
  data?: T;
  message?: string;
};

function ajaxUrl(config: MrtAjaxConfig): string {
  return config.ajaxurl || '/wp-admin/admin-ajax.php';
}

function nonce(config: MrtAjaxConfig): string {
  return config.nonce || '';
}

export async function mrtPost<T>(
  config: MrtAjaxConfig,
  action: string,
  data: Record<string, string | number> = {},
): Promise<MrtAjaxResponse<T>> {
  const body = new URLSearchParams({
    action,
    nonce: nonce(config),
    ...Object.fromEntries(
      Object.entries(data).map(([k, v]) => [k, String(v)]),
    ),
  });

  const res = await fetch(ajaxUrl(config), {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body,
    credentials: 'same-origin',
  });

  if (!res.ok) {
    return { success: false, message: 'Nätverksfel' };
  }

  const json = (await res.json()) as {
    success: boolean;
    data?: T;
    message?: string;
  };

  if (!json.success) {
    const failMsg =
      json.data && typeof json.data === 'object' && 'message' in json.data
        ? String((json.data as { message: string }).message)
        : json.message || 'Begäran misslyckades';
    return { success: false, message: failMsg };
  }

  return { success: true, data: json.data };
}

