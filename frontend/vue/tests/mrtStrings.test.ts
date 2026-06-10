import { describe, expect, it } from 'vitest';
import { resolveMrtString } from '../src/utils/mrtStrings';

describe('resolveMrtString', () => {
  it('prefers strings then wizard then labels', () => {
    expect(
      resolveMrtString(
        {
          strings: { loading: 'A' },
          wizard: { loading: 'B' },
          labels: { loading: 'C' },
        },
        'loading',
        'fallback',
      ),
    ).toBe('A');

    expect(
      resolveMrtString({ wizard: { loading: 'B' }, labels: { loading: 'C' } }, 'loading', 'fallback'),
    ).toBe('B');

    expect(resolveMrtString({ labels: { loading: 'C' } }, 'loading', 'fallback')).toBe('C');
  });

  it('returns fallback when missing', () => {
    expect(resolveMrtString({}, 'missing', 'fallback')).toBe('fallback');
  });
});
