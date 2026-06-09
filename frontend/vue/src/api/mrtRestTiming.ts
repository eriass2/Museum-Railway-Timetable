import type { MrtRestRequestInit } from './mrtRest';
import type { MrtRestConfig } from '../config/types';
import { configureMrtLog, mrtLog, resolveMrtLogSource } from '../utils/mrtLog';

function summarizeLegsParam(value: string | number): string {
  if (typeof value !== 'string' || value === '') {
    return '';
  }
  try {
    const parsed = JSON.parse(value) as unknown;
    return Array.isArray(parsed) ? `legs:${parsed.length}` : 'legs:?';
  } catch {
    return 'legs:?';
  }
}

function queryTimingPart(query: Record<string, string | number>): string {
  const parts: string[] = [];
  const keys = Object.keys(query).sort();
  for (const key of keys) {
    const raw = query[key];
    if (raw === undefined || raw === '') {
      continue;
    }
    if (key === 'outbound_legs' || key === 'inbound_legs') {
      parts.push(`${key}=${summarizeLegsParam(raw)}`);
      continue;
    }
    parts.push(`${key}=${raw}`);
  }
  return parts.join('&');
}

function bodyTimingPart(body: Record<string, unknown>): string {
  const parts: string[] = [];
  const pick = (key: string, field: string) => {
    const value = body[field];
    if (value !== undefined && value !== null && value !== '') {
      parts.push(`${key}=${String(value)}`);
    }
  };
  pick('from', 'from_station');
  pick('to', 'to_station');
  pick('date', 'date');
  pick('year', 'year');
  pick('month', 'month');
  pick('trip_type', 'trip_type');
  pick('service_id', 'service_id');
  return parts.join('&');
}

/** Stable REST cache key for dev timing (no free-text / PII). */
export function buildMrtRestTimingKey(init: MrtRestRequestInit): string {
  const path = init.path.replace(/^\/+/, '');
  const segments = [`${init.method} ${path}`];
  if (init.query && Object.keys(init.query).length > 0) {
    const q = queryTimingPart(init.query);
    if (q) {
      segments.push(q);
    }
  }
  if (init.body && Object.keys(init.body).length > 0) {
    const b = bodyTimingPart(init.body);
    if (b) {
      segments.push(b);
    }
  }
  return segments.join(' | ');
}

export function logMrtRestTiming(
  config: MrtRestConfig,
  init: MrtRestRequestInit,
  durationMs: number,
  ok: boolean,
): void {
  if (!config.isDevMode) {
    return;
  }
  configureMrtLog({
    isDevMode: true,
    defaultSource: resolveMrtLogSource(config),
  });
  const rounded = Math.round(durationMs);
  mrtLog({
    level: 'info',
    source: resolveMrtLogSource(config),
    message: `REST ${rounded}ms ${ok ? 'ok' : 'fail'} ${init.method} ${init.path.replace(/^\/+/, '')}`,
    context: {
      key: buildMrtRestTimingKey(init),
      durationMs: rounded,
      ok,
    },
  });
}
