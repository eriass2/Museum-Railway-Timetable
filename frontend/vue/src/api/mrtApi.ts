import type { MrtVueConfig } from '../useMrtConfig';

export type MrtAjaxResponse<T> = {
  success: boolean;
  data?: T;
  message?: string;
};

function ajaxUrl(config: MrtVueConfig): string {
  return (config.ajaxurl as string) || '/wp-admin/admin-ajax.php';
}

function nonce(config: MrtVueConfig): string {
  return (config.nonce as string) || '';
}

export async function mrtPost<T>(
  config: MrtVueConfig,
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
    return { success: false, message: 'Network error' };
  }

  const json = (await res.json()) as {
    success: boolean;
    data?: T;
    message?: string;
  };

  if (!json.success) {
    const msg =
      json.data && typeof json.data === 'object' && 'message' in json.data
        ? String((json.data as { message: string }).message)
        : json.message || 'Request failed';
    return { success: false, message: msg };
  }

  return { success: true, data: json.data };
}

export function msg(config: MrtVueConfig, key: string, fallback = ''): string {
  const strings = config.strings as Record<string, string> | undefined;
  if (strings && strings[key]) {
    return strings[key];
  }
  const wizard = config.wizard as Record<string, string> | undefined;
  if (wizard && wizard[key]) {
    return wizard[key];
  }
  return fallback;
}
