import { describe, expect, it } from 'vitest';
import { buildTimelineItems } from '../../src/components/ui/timeline/buildTimelineItems';

const stops = [
  { station_title: 'A' },
  { station_title: 'B' },
  { station_title: 'C' },
  { station_title: 'D' },
];

describe('buildTimelineItems', () => {
  it('returns all stops without toggle when there are two or fewer', () => {
    const rows = buildTimelineItems(
      [{ station_title: 'A' }, { station_title: 'B' }],
      false,
    );
    expect(rows).toHaveLength(2);
    expect(rows.every((row) => row.kind === 'stop')).toBe(true);
    expect(rows[0]).toMatchObject({ position: 'first' });
    expect(rows[1]).toMatchObject({ position: 'last' });
  });

  it('marks a single stop as only', () => {
    const rows = buildTimelineItems([{ station_title: 'A' }], false);
    expect(rows).toHaveLength(1);
    expect(rows[0]).toMatchObject({ kind: 'stop', position: 'only' });
  });

  it('inserts toggle between first and last when collapsed', () => {
    const rows = buildTimelineItems(stops, false);
    expect(rows.map((row) => row.kind)).toEqual(['stop', 'toggle', 'stop']);
    expect(rows[0]).toMatchObject({ position: 'first' });
    expect(rows[2]).toMatchObject({ position: 'last' });
  });

  it('includes middle stops when expanded', () => {
    const rows = buildTimelineItems(stops, true);
    expect(rows.map((row) => row.kind)).toEqual([
      'stop',
      'toggle',
      'stop',
      'stop',
      'stop',
    ]);
    expect(rows[2]).toMatchObject({ position: 'middle', stop: { station_title: 'B' } });
    expect(rows[3]).toMatchObject({ position: 'middle', stop: { station_title: 'C' } });
  });
});
