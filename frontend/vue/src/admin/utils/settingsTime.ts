/** Convert minutes-from-midnight to HTML time input value (HH:MM). */
export function minutesToTimeInput(minutes: number): string {
  const clamped = Math.max(0, Math.min(1439, Math.floor(minutes)));
  const h = Math.floor(clamped / 60);
  const m = clamped % 60;
  return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
}

/** Parse HTML time input (HH:MM) to minutes-from-midnight. */
export function timeInputToMinutes(value: string): number {
  const match = /^(\d{1,2}):(\d{2})$/.exec(value.trim());
  if (!match) {
    return 900;
  }
  const h = parseInt(match[1], 10);
  const m = parseInt(match[2], 10);
  if (h < 0 || h > 23 || m < 0 || m > 59) {
    return 900;
  }
  return h * 60 + m;
}
