export type MrtTimelineStop = {
  station_title?: string;
  departure_time?: string;
  arrival_time?: string;
  on_request_pickup?: boolean;
  on_request_dropoff?: boolean;
  on_request_both?: boolean;
};

export type TimelineItem =
  | { kind: 'stop'; stop: MrtTimelineStop; terminal: boolean; key: string }
  | { kind: 'toggle' };

export function buildTimelineItems(
  stops: MrtTimelineStop[],
  showAllStops: boolean,
): TimelineItem[] {
  if (stops.length <= 2) {
    return stops.map((stop, index) => ({
      kind: 'stop' as const,
      stop,
      terminal: index === 0 || index === stops.length - 1,
      key: `stop-${index}`,
    }));
  }

  const items: TimelineItem[] = [
    { kind: 'stop', stop: stops[0], terminal: true, key: 'stop-first' },
    { kind: 'toggle' },
  ];

  if (showAllStops) {
    stops.slice(1, -1).forEach((stop, index) => {
      items.push({
        kind: 'stop',
        stop,
        terminal: false,
        key: `stop-mid-${index}`,
      });
    });
  }

  items.push({
    kind: 'stop',
    stop: stops[stops.length - 1],
    terminal: true,
    key: 'stop-last',
  });

  return items;
}
