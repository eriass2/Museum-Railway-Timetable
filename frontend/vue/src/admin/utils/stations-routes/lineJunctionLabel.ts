import type { LineRow } from '../../types';

export function lineJunctionLabel(
  line: Pick<LineRow, 'junction_station_name'>,
  emptyLabel = '—',
): string {
  return line.junction_station_name || emptyLabel;
}
