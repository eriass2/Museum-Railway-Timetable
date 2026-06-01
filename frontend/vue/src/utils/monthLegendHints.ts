/** Legend footnotes under the month calendar (G5: count hint only when show_counts). */
export function monthLegendHints(
  showCounts: boolean,
  legendCountHint?: string,
  legendClickHint?: string,
): string[] {
  const hints: string[] = [];
  if (showCounts && legendCountHint) {
    hints.push(`(${legendCountHint})`);
  }
  if (legendClickHint) {
    hints.push(`(${legendClickHint})`);
  }
  return hints;
}
