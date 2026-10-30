import { hhmmToMinutes, minutesToHhmm } from '../../utils/datetime';

/** Convert a clock time to an HTML time input. HTML time cannot pass 23:59. */
export function minutesToTimeInput(minutes: number): string {
  const clock = Math.max(0, Math.min(23 * 60 + 59, Math.floor(minutes)));
  return minutesToHhmm(clock);
}

/** Parse HTML time input (HH:MM) to minutes-from-midnight. */
export function timeInputToMinutes(value: string): number {
  return hhmmToMinutes(value) ?? 900;
}
