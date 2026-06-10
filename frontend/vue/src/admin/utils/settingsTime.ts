import { hhmmToMinutes, minutesToHhmm } from '../../utils/datetime';

/** Convert minutes-from-midnight to HTML time input value (HH:MM). */
export function minutesToTimeInput(minutes: number): string {
  return minutesToHhmm(minutes);
}

/** Parse HTML time input (HH:MM) to minutes-from-midnight. */
export function timeInputToMinutes(value: string): number {
  return hhmmToMinutes(value) ?? 900;
}
