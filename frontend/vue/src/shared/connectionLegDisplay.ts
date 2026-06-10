import type { JourneyConnection, JourneyLeg } from './journey';
import { waitMinutesBetween } from './tripClock';

export type ConnectionLegDisplay = {
  vehicleLabel: string;
  iconUrl: string;
  kind: string;
  timeRange: string;
  route: string;
};

export type ConnectionLegSummaryItem =
  | { type: 'leg'; leg: ConnectionLegDisplay }
  | { type: 'transfer'; label: string };

export type TransferLabelStrings = {
  changeAt: string;
  transferTrip: string;
};

export type StationTitleLookup = (stationId?: number) => string;

export function stationTitleLookup(
  stations: { id: number; title: string }[],
): StationTitleLookup {
  const byId = new Map(stations.map((s) => [s.id, s.title]));
  return (stationId?: number) => (stationId ? byId.get(stationId) || '' : '');
}

export function transferWaitMinutes(
  legBefore: JourneyLeg,
  legAfter: JourneyLeg,
  _connection: JourneyConnection,
): number | null {
  return waitMinutesBetween(legBefore.to_arrival || '', legAfter.from_departure || '');
}

export function transferStationId(
  legBefore: JourneyLeg,
  legAfter: JourneyLeg,
  connection: JourneyConnection,
): number | undefined {
  return legBefore.to_station_id ?? connection.transfer_station_id ?? legAfter.from_station_id;
}

/** Unified transfer label (summary, detail expand, share text). */
export function buildTransferLabel(
  legBefore: JourneyLeg,
  legAfter: JourneyLeg,
  connection: JourneyConnection,
  stationTitle: StationTitleLookup,
  strings: TransferLabelStrings,
): string {
  const xferId = transferStationId(legBefore, legAfter, connection);
  const station = xferId ? stationTitle(xferId) : '';
  const wait = transferWaitMinutes(legBefore, legAfter, connection);

  const base = station
    ? strings.changeAt.replace('%s', station)
    : strings.transferTrip;

  if (wait !== null && wait !== undefined && !Number.isNaN(Number(wait))) {
    return `${base} · ${wait} min`;
  }
  return base;
}

/** Hide leg list for direct trips; show when there are transfers or multiple legs. */
export function shouldShowConnectionLegList(items: ConnectionLegSummaryItem[]): boolean {
  if (items.length === 0) {
    return false;
  }
  if (items.some((item) => item.type === 'transfer')) {
    return true;
  }
  return items.length > 1;
}
