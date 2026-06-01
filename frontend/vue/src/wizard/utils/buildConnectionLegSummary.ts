import type { ConnectionLegDisplay, ConnectionLegSummaryItem, StationTitleLookup } from '../../shared/connectionLegDisplay';
import { buildTransferLabel } from '../../shared/connectionLegDisplay';
import type { JourneyConnection, JourneyLeg } from '../../shared/journey';
import { formatTripClock } from '../../shared/tripClock';
import type { WizardCfg } from './wizardCfgTypes';
import { connectionLegs } from './connection';
import { legToVehicleItem } from './vehicle';
import { cfgStr } from './wizardLabels';

function transferStrings(cfg: WizardCfg) {
  return {
    changeAt: cfgStr(cfg, 'changeAt', 'Byte vid %s'),
    transferTrip: cfgStr(cfg, 'transferTrip', 'Byte'),
  };
}

function legRouteLabel(leg: JourneyLeg, stationTitle: StationTitleLookup): string {
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
  stationTitle: StationTitleLookup,
  cfg: WizardCfg,
): ConnectionLegDisplay {
  const vehicle = legToVehicleItem(leg, cfg);
  return {
    vehicleLabel: vehicle.label,
    iconUrl: vehicle.iconUrl || '',
    kind: vehicle.kind,
    timeRange: legTimeRange(leg),
    route: legRouteLabel(leg, stationTitle),
  };
}

export function buildConnectionLegSummary(
  connection: JourneyConnection,
  stationTitle: StationTitleLookup,
  cfg: WizardCfg,
): ConnectionLegSummaryItem[] {
  const legs = connectionLegs(connection);
  if (!legs.length) {
    return [];
  }

  const strings = transferStrings(cfg);
  const items: ConnectionLegSummaryItem[] = [];
  legs.forEach((leg, index) => {
    if (index > 0) {
      items.push({
        type: 'transfer',
        label: buildTransferLabel(legs[index - 1], leg, connection, stationTitle, strings),
      });
    }
    items.push({
      type: 'leg',
      leg: legDisplay(leg, stationTitle, cfg),
    });
  });
  return items;
}
