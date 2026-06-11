export function formatTripLineDisplay(
  lineName: string | undefined,
  routeName: string | undefined,
): string {
  return lineName || routeName || '—';
}
