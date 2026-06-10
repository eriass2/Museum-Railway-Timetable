import { describe, expect, it } from 'vitest';
import { formatOverviewTimePrefix, formatOverviewTimeValue, parseOverviewTimeText } from '../src/utils/overviewTimeDisplay';

describe('parseOverviewTimeText', () => {
  it('parses P and Ca before a time; Ca is rendered with the digits', () => {
    const parts = parseOverviewTimeText('P Ca 11.13');
    expect(parts).toEqual({
      restrictions: ['P'],
      approximate: true,
      value: '11.13',
    });
    expect(formatOverviewTimePrefix(parts)).toBe('P ');
    expect(formatOverviewTimeValue(parts)).toBe('Ca 11.13');
  });

  it('accepts legacy Ca-before-P strings and keeps Ca next to the time', () => {
    const parts = parseOverviewTimeText('Ca P 11.13');
    expect(parts).toEqual({
      restrictions: ['P'],
      approximate: true,
      value: '11.13',
    });
    expect(formatOverviewTimePrefix(parts)).toBe('P ');
    expect(formatOverviewTimeValue(parts)).toBe('Ca 11.13');
  });

  it('keeps a plain time as value only', () => {
    expect(parseOverviewTimeText('10.00')).toEqual({
      restrictions: [],
      approximate: false,
      value: '10.00',
    });
  });

  it('keeps special symbols as value only', () => {
    expect(parseOverviewTimeText('X')).toEqual({
      restrictions: [],
      approximate: false,
      value: 'X',
    });
    expect(parseOverviewTimeText('|')).toEqual({
      restrictions: [],
      approximate: false,
      value: '|',
    });
    expect(parseOverviewTimeText('—')).toEqual({
      restrictions: [],
      approximate: false,
      value: '—',
    });
  });
});
