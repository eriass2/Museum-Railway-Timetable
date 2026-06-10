import { describe, expect, it } from 'vitest';
import { buildMonthGrid } from '../src/utils/monthGrid';

describe('buildMonthGrid', () => {
  it('leads with empty cells for Monday-start weeks', () => {
    const cells = buildMonthGrid(28, 3, 0, true, {});
    expect(cells[0]).toEqual({ kind: 'empty' });
    expect(cells.filter((c) => c.kind === 'day')).toHaveLength(28);
  });

  it('attaches date meta when provided', () => {
    const cells = buildMonthGrid(1, 1, 1, false, {
      1: { ymd: '2026-05-01', count: 2, running: true, type: 'green' },
    });
    const day = cells.find((c) => c.kind === 'day');
    expect(day && day.kind === 'day' ? day.info.count : 0).toBe(2);
  });

  it('pads to minWeekRows for stable calendar height', () => {
    const cells = buildMonthGrid(30, 2, 2, true, {}, { minWeekRows: 6 });
    expect(cells).toHaveLength(42);
  });
});
