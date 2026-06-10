import { describe, expect, it } from 'vitest';
import { formatOverviewTimePrefix, parseOverviewTimeText } from '../src/utils/overviewTimeDisplay';

describe('parseOverviewTimeText', () => {
  it('parses P and Ca before a time with Ca closest to digits in display order', () => {
    expect(parseOverviewTimeText('P Ca 11.13')).toEqual({
      restrictions: ['P'],
      approximate: true,
      value: '11.13',
    });
    expect(formatOverviewTimePrefix(parseOverviewTimeText('P Ca 11.13'))).toBe('P Ca ');
  });

  it('accepts legacy Ca-before-P strings and still orders P before Ca', () => {
    expect(parseOverviewTimeText('Ca P 11.13')).toEqual({
      restrictions: ['P'],
      approximate: true,
      value: '11.13',
    });
    expect(formatOverviewTimePrefix(parseOverviewTimeText('Ca P 11.13'))).toBe('P Ca ');
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
