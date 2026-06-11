export function directionStillValid(
  towardStationId: number,
  termini: { station_id: number }[],
): boolean {
  return termini.some((term) => term.station_id === towardStationId);
}
