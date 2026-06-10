/** Display label for a stored price zone id (1→A … 4→D). */
export function formatPriceZoneLabel(zone: number): string {
  if (Number.isInteger(zone) && zone >= 1 && zone <= 4) {
    return String.fromCharCode(64 + zone);
  }
  return String(zone);
}

export function formatPriceZoneList(zones: number[]): string {
  if (!zones.length) {
    return '—';
  }
  return zones.map(formatPriceZoneLabel).join(', ');
}

/** Journey zone count for public price suffix (e.g. 3 → "zon A–C"). */
export function formatPriceZoneSpan(zoneCount: number): string {
  if (!Number.isInteger(zoneCount) || zoneCount < 1) {
    return '';
  }
  if (zoneCount <= 4) {
    const end = formatPriceZoneLabel(zoneCount);
    return zoneCount === 1 ? `zon ${end}` : `zon A–${end}`;
  }
  return `${zoneCount} zoner`;
}
