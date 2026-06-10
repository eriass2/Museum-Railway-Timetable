import { describe, expect, it } from 'vitest';
import { parseOverviewTimeText } from '../src/utils/overviewTimeDisplay';

describe('parseOverviewTimeText', () => {
  it('splits Ca and P prefixes from a time', () => {
    expect(parseOverviewTimeText('Ca P 11.13')).toEqual({
      labels: ['Ca', 'P'],
      value: '11.13',
    });
  });

  it('splits Ca and A prefixes from a time', () => {
    expect(parseOverviewTimeText('Ca A 09.30')).toEqual({
      labels: ['Ca', 'A'],
      value: '09.30',
    });
  });

  it('keeps a plain time as value only', () => {
    expect(parseOverviewTimeText('10.00')).toEqual({
      labels: [],
      value: '10.00',
    });
  });

  it('keeps special symbols as value only', () => {
    expect(parseOverviewTimeText('X')).toEqual({ labels: [], value: 'X' });
    expect(parseOverviewTimeText('|')).toEqual({ labels: [], value: '|' });
    expect(parseOverviewTimeText('—')).toEqual({ labels: [], value: '—' });
  });
});
