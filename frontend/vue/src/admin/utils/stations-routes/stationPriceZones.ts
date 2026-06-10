import { formatPriceZoneList } from '../../../shared/priceZoneLabels';
import type { StationRow } from '../../types';

export const DEFAULT_STATION_PRICE_ZONE_OPTIONS = [1, 2, 3, 4] as const;

/** @deprecated Use resolveStationPriceZoneOptions from price schema. */
export const STATION_PRICE_ZONE_OPTIONS = DEFAULT_STATION_PRICE_ZONE_OPTIONS;

export function resolveStationPriceZoneOptions(zones: number[] | undefined): number[] {
  if (zones?.length) {
    return [...zones].sort((a, b) => a - b);
  }
  return [...DEFAULT_STATION_PRICE_ZONE_OPTIONS];
}

export function stationMissingPriceZone(st: StationRow): boolean {
  return (st.price_zones ?? []).length === 0;
}

export function stationHasPriceZone(st: StationRow, zone: number): boolean {
  return (st.price_zones ?? []).includes(zone);
}

export function toggleStationPriceZone(st: StationRow, zone: number): void {
  const current = [...(st.price_zones ?? [])];
  const index = current.indexOf(zone);
  if (index >= 0) {
    current.splice(index, 1);
  } else if (current.length < 2) {
    current.push(zone);
    current.sort((a, b) => a - b);
  }
  st.price_zones = current;
}

export function formatStationPriceZones(zones: number[] | undefined): string {
  return formatPriceZoneList(zones ?? []);
}
