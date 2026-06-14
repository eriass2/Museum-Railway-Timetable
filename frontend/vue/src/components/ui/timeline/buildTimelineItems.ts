import type { MrtTimelineStop, StopPosition, TimelineRow } from './types';

function stopPosition(index: number, total: number): StopPosition {
  if (total === 1) {
    return 'only';
  }
  if (index === 0) {
    return 'first';
  }
  if (index === total - 1) {
    return 'last';
  }
  return 'middle';
}

export function buildTimelineItems(
  stops: MrtTimelineStop[],
  showAllStops: boolean,
): TimelineRow[] {
  if (stops.length <= 2) {
    return stops.map((stop, index) => ({
      kind: 'stop' as const,
      stop,
      position: stopPosition(index, stops.length),
      key: `stop-${index}`,
    }));
  }

  const items: TimelineRow[] = [
    {
      kind: 'stop',
      stop: stops[0],
      position: 'first',
      key: 'stop-first',
    },
    { kind: 'toggle' },
  ];

  if (showAllStops) {
    stops.slice(1, -1).forEach((stop, index) => {
      items.push({
        kind: 'stop',
        stop,
        position: 'middle',
        key: `stop-mid-${index}`,
      });
    });
  }

  items.push({
    kind: 'stop',
    stop: stops[stops.length - 1],
    position: 'last',
    key: 'stop-last',
  });

  return items;
}
