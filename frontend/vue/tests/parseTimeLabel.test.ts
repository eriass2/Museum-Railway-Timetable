import { describe, expect, it } from 'vitest';
import { parseTimeLabelCaPrefix } from '../src/shared/parseTimeLabel';

describe('parseTimeLabelCaPrefix', () => {
  it('splits Ca prefix from clock value', () => {
    expect(parseTimeLabelCaPrefix('Ca 10.46')).toEqual({ ca: true, value: '10.46' });
  });

  it('returns plain clock without Ca flag', () => {
    expect(parseTimeLabelCaPrefix('10.00')).toEqual({ ca: false, value: '10.00' });
  });

  it('trims surrounding whitespace', () => {
    expect(parseTimeLabelCaPrefix('  Ca 09.30  ')).toEqual({ ca: true, value: '09.30' });
  });
});
