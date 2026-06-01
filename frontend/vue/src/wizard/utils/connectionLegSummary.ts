import type { JourneyConnection, JourneyLeg } from '../types';
import type { WizardCfg } from './wizardCfgTypes';
import { connectionLegs } from './connection';
import { formatTripClock } from './format';
import { legVehicleKind, legVehicleLabel, trainIconUrl } from './vehicle';
import { cfgStr } from './wizardLabels';
import { parseTripClock } from '../../shared/prices';

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

export function stationTitleLookup(
  stations: { id: number; title: string }[],
): (stationId?: number) => string {
  const byId = new Map(stations.map((s) => [s.id, s.title]));
  return (stationId?: number) => (stationId ? byId.get(stationId) || '' : '');
}

export function waitMinutesBetween(arrival: string, departure: string): number | null {
  const arr = parseTripClock(arrival);
  const dep = parseTripClock(departure);
  if (arr === null || dep === null || dep < arr) {
    return null;
  }
  return dep - arr;
}

function legRouteLabel(
  leg: JourneyLeg,
  stationTitle: (id?: number) => string,
): string {
  const from = stationTitle(leg.from_station_id);
  const to = stationTitle(leg.to_station_id);
  if (from && to) {
    return `${from} → ${to}`;
  }
  return '';
}

function legTimeRange(leg: JourneyLeg): string {
  const from = formatTripClock(leg.from_departure || '');
  const to = formatTripClock(leg.to_arrival || '');
  if (from && to) {
    return `${from} – ${to}`;
  }
  return from || to;
}

function legDisplay(
  leg: JourneyLeg,
  stationTitle: (id?: number) => string,
  cfg: WizardCfg,
): ConnectionLegDisplay {
  const kind = legVehicleKind(leg, cfg);
  return {
    vehicleLabel: legVehicleLabel(leg, cfg),
    iconUrl: trainIconUrl(kind, cfg),
    kind,
    timeRange: legTimeRange(leg),
    route: legRouteLabel(leg, stationTitle),
  };
}

export function transferLabelBetween(
  legBefore: JourneyLeg,
  legAfter: JourneyLeg,
  connection: JourneyConnection,
  stationTitle: (id?: number) => string,
  cfg: WizardCfg,
): string {
  const xferId =
    legBefore.to_station_id ?? connection.transfer_station_id ?? legAfter.from_station_id;
  const station = xferId ? stationTitle(xferId) : '';
  const wait =
    connection.transfer_wait_minutes ??
    waitMinutesBetween(legBefore.to_arrival || '', legAfter.from_departure || '');

  const base = station
    ? cfgStr(cfg, 'changeAt', 'Byte vid %s').replace('%s', station)
    : cfgStr(cfg, 'transferTrip', 'Byte');

  if (wait !== null && wait !== undefined && !Number.isNaN(Number(wait))) {
    return `${base} · ${wait} min`;
  }
  return base;
}

export function buildConnectionLegSummary(
  connection: JourneyConnection,
  stationTitle: (id?: number) => string,
  cfg: WizardCfg,
): ConnectionLegSummaryItem[] {
  const legs = connectionLegs(connection);
  if (!legs.length) {
    return [];
  }

  const items: ConnectionLegSummaryItem[] = [];
  legs.forEach((leg, index) => {
    if (index > 0) {
      items.push({
        type: 'transfer',
        label: transferLabelBetween(legs[index - 1], leg, connection, stationTitle, cfg),
      });
    }
    items.push({
      type: 'leg',
      leg: legDisplay(leg, stationTitle, cfg),
    });
  });
  return items;
}
