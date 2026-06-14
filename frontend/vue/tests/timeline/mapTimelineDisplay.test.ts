import { describe, expect, it } from 'vitest';
import { mapTimelineDisplay } from '../../src/components/ui/timeline/mapTimelineDisplay';
import type { TimelineRow } from '../../src/components/ui/timeline/types';

describe('mapTimelineDisplay', () => {
  it('enriches stop rows with display fields', () => {
    const rows: TimelineRow[] = [
      {
        kind: 'stop',
        key: 'stop-first',
        position: 'first',
        stop: { station_title: 'Uppsala', on_request_pickup: true },
      },
      { kind: 'toggle' },
      {
        kind: 'stop',
        key: 'stop-last',
        position: 'last',
        stop: { station_title: 'Årsta' },
      },
    ];

    const display = mapTimelineDisplay(rows, () => 'Ca 10.46');

    expect(display[0]).toMatchObject({
      kind: 'stop',
      position: 'first',
      stationTitle: 'Uppsala',
      showInfo: true,
      timeParts: { ca: true, value: '10.46' },
    });
    expect(display[1]).toEqual({ kind: 'toggle' });
    expect(display[2]).toMatchObject({
      kind: 'stop',
      position: 'last',
      stationTitle: 'Årsta',
      showInfo: false,
    });
  });

  it('maps middle stops without duplicating presentation fields', () => {
    const rows: TimelineRow[] = [
      {
        kind: 'stop',
        key: 'stop-mid-0',
        position: 'middle',
        stop: { station_title: 'Lövstahagen', approximate_time: true },
      },
    ];

    const display = mapTimelineDisplay(rows, () => 'Ca 10.46');

    expect(display[0]).toEqual({
      kind: 'stop',
      key: 'stop-mid-0',
      position: 'middle',
      timeParts: { ca: true, value: '10.46' },
      stationTitle: 'Lövstahagen',
      showInfo: false,
    });
    expect(display[0]).not.toHaveProperty('lineSegment');
    expect(display[0]).not.toHaveProperty('terminal');
  });
});
