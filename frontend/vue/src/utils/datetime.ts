/** Shared date/time helpers (mirror inc/domain/datetime/datetime.php where applicable). */

const YMD_RE = /^\d{4}-\d{2}-\d{2}$/;
const HHMM_RE = /^(?:[01]\d|2[0-3]):[0-5]\d$/;
const HHMM_LOOSE_RE = /^\d{1,2}:\d{2}$/;

export function validateYmd(value: string): boolean {
  return YMD_RE.test(value);
}

export function validateHhmm(value: string): boolean {
  return value === '' || HHMM_RE.test(value);
}

export function hhmmToMinutes(hhmm: string): number | null {
  const trimmed = hhmm.trim();
  if (trimmed === '' || !HHMM_RE.test(trimmed)) {
    return null;
  }
  const [h, m] = trimmed.split(':').map((part) => Number.parseInt(part, 10));
  return h * 60 + m;
}

export function minutesToHhmm(minutes: number): string {
  const clamped = Math.max(0, Math.min(1439, Math.floor(minutes)));
  const h = Math.floor(clamped / 60);
  const m = clamped % 60;
  return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
}

/** Normalize loose H:MM or HH:MM to HH:MM; empty string if invalid. */
export function padHhmm(value: string): string {
  if (!value || !HHMM_LOOSE_RE.test(value)) {
    return '';
  }
  const [h, m] = value.split(':');
  return `${h.padStart(2, '0')}:${m}`;
}

export function todayYmd(): string {
  const now = new Date();
  const y = now.getFullYear();
  const m = String(now.getMonth() + 1).padStart(2, '0');
  const d = String(now.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

export function formatYmdForDisplay(ymd: string, monthNames?: string[]): string {
  const parts = ymd.split('-');
  if (parts.length !== 3) {
    return ymd;
  }
  const month = Number.parseInt(parts[1], 10);
  const day = Number.parseInt(parts[2], 10);
  if (monthNames && monthNames[month - 1]) {
    return `${day} ${monthNames[month - 1]} ${parts[0]}`;
  }
  return ymd;
}

/** Display clock with dot separator (matches PHP MRT_format_time_display). */
export function formatHhmmForDisplay(time: string): string {
  return time ? time.replace(':', '.') : '—';
}
