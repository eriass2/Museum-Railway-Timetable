/** Pad calendar cells to full weeks (shared by month view and wizard). */
export function chunkWeekRows<T>(cells: T[]): T[][] {
  const rows: T[][] = [];
  for (let i = 0; i < cells.length; i += 7) {
    rows.push(cells.slice(i, i + 7));
  }
  return rows;
}

export function orderedWeekdayHeaders(
  abbrev: string[],
  startOfWeek: number,
): string[] {
  if (!abbrev.length) {
    return [];
  }
  const start = Math.max(0, Math.min(6, startOfWeek));
  const out: string[] = [];
  for (let i = 0; i < 7; i++) {
    out.push(abbrev[(start + i) % abbrev.length] || '');
  }
  return out;
}
