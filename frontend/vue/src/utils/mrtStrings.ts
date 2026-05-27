import type { MrtAjaxConfig } from '../config/types';

export type MrtStringSources = Pick<MrtAjaxConfig, 'strings'> & {
  wizard?: Record<string, unknown>;
  labels?: Record<string, string>;
};

function readString(value: unknown): string | null {
  return typeof value === 'string' && value.length > 0 ? value : null;
}

/** Resolve a localized string from PHP config (strings, wizard, labels). */
export function resolveMrtString(
  sources: MrtStringSources,
  key: string,
  fallback = '',
): string {
  return (
    readString(sources.strings?.[key]) ??
    readString(sources.wizard?.[key]) ??
    readString(sources.labels?.[key]) ??
    fallback
  );
}
