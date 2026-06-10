import { describe, expect, it } from 'vitest';
import { chunkWeekRows, orderedWeekdayHeaders } from '../src/utils/calendarGrid';

describe('chunkWeekRows', () => {
  it('splits into weeks of 7', () => {
    const flat = Array.from({ length: 14 }, (_, i) => i);
    expect(chunkWeekRows(flat)).toEqual([
      [0, 1, 2, 3, 4, 5, 6],
      [7, 8, 9, 10, 11, 12, 13],
    ]);
  });
});

describe('orderedWeekdayHeaders', () => {
  it('rotates from startOfWeek', () => {
    const abbrev = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
    expect(orderedWeekdayHeaders(abbrev, 1)).toEqual(['M', 'T', 'W', 'T', 'F', 'S', 'S']);
  });
});
