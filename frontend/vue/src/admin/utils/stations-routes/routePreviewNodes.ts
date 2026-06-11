export type RoutePreviewNode = {
  id: number;
  name: string;
  station_type: string;
  role: 'start' | 'end' | 'via' | 'both';
};

const STATION_TYPE_STRING_KEYS: Record<string, string> = {
  station: 'stationsTypeStation',
  halt: 'stationsTypeHalt',
  depot: 'stationsTypeDepot',
  museum: 'stationsTypeMuseum',
};

export const STATION_TYPE_OPTIONS = Object.entries(STATION_TYPE_STRING_KEYS).map(
  ([value, labelKey]) => ({ value, labelKey }),
);

const ROUTE_PREVIEW_ROLE_STRING_KEYS: Record<RoutePreviewNode['role'], string> = {
  start: 'routePreviewStart',
  end: 'routePreviewEnd',
  both: 'routePreviewBoth',
  via: '',
};

export function routePreviewRoleLabel(
  role: RoutePreviewNode['role'],
  labelFor?: (key: string) => string,
): string {
  if (role === 'via' || !labelFor) {
    return '';
  }
  const stringKey = ROUTE_PREVIEW_ROLE_STRING_KEYS[role];
  return stringKey ? labelFor(stringKey) : '';
}

export function routePreviewTypeLabel(
  stationType: string,
  labelFor?: (key: string) => string,
): string {
  if (!stationType) {
    return '';
  }
  const stringKey = STATION_TYPE_STRING_KEYS[stationType];
  if (stringKey && labelFor) {
    const translated = labelFor(stringKey);
    if (translated) {
      return translated;
    }
  }
  return stationType;
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
