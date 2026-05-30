export type RoutePreviewNode = {
  id: number;
  name: string;
  station_type: string;
  role: 'start' | 'end' | 'via' | 'both';
};

const TYPE_LABELS: Record<string, string> = {
  station: 'Station',
  halt: 'Hållplats',
  depot: 'Depot',
  museum: 'Museum',
};

export function routePreviewTypeLabel(stationType: string): string {
  if (!stationType) {
    return '';
  }
  return TYPE_LABELS[stationType] || stationType;
}

export function buildRoutePreviewNodes(
  stationIds: number[],
  stationsById: Map<number, { title: string; station_type?: string }>,
  startStationId = 0,
  endStationId = 0,
): RoutePreviewNode[] {
  return stationIds.map((id) => {
    const meta = stationsById.get(id);
    const isStart = startStationId > 0 && id === startStationId;
    const isEnd = endStationId > 0 && id === endStationId;
    let role: RoutePreviewNode['role'] = 'via';
    if (isStart && isEnd) {
      role = 'both';
    } else if (isStart) {
      role = 'start';
    } else if (isEnd) {
      role = 'end';
    }
    return {
      id,
      name: meta?.title || `#${id}`,
      station_type: meta?.station_type || '',
      role,
    };
  });
}
