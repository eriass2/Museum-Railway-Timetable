import { describe, expect, it } from 'vitest';
import {
  formatOverviewTimeSuffix,
  formatOverviewTimeValue,
  parseOverviewTimeText,
} from '../src/utils/overviewTimeDisplay';

describe('parseOverviewTimeText', () => {
  it('parses Ca before time and X suffix', () => {
    const parts = parseOverviewTimeText('Ca 10.09 X');
    expect(parts).toEqual({
      approximate: true,
      value: '10.09',
      suffix: 'X',
    });
    expect(formatOverviewTimeValue(parts)).toBe('Ca 10.09');
    expect(formatOverviewTimeSuffix(parts)).toBe('X');
  });

  it('parses Ca before time and P suffix', () => {
    const parts = parseOverviewTimeText('Ca 11.13 P');
    expect(parts).toEqual({
      approximate: true,
      value: '11.13',
      suffix: 'P',
    });
  });

  it('accepts legacy prefix strings and normalizes to suffix layout', () => {
    const parts = parseOverviewTimeText('P Ca 11.13');
    expect(parts).toEqual({
      approximate: true,
      value: '11.13',
      suffix: 'P',
    });
  });

  it('keeps a plain time as value only', () => {
    expect(parseOverviewTimeText('10.00')).toEqual({
      approximate: false,
      value: '10.00',
      suffix: '',
    });
  });

  it('keeps special symbols as value only', () => {
    expect(parseOverviewTimeText('X')).toEqual({
      approximate: false,
      value: 'X',
      suffix: '',
    });
    expect(parseOverviewTimeText('|')).toEqual({
      approximate: false,
      value: '|',
      suffix: '',
    });
    expect(parseOverviewTimeText('—')).toEqual({
      approximate: false,
      value: '—',
      suffix: '',
    });
  });
});
