import { describe, expect, it } from 'vitest';
import { buildStepLabels, buildStepSequence } from '../src/wizard/store/wizardSteps';

describe('buildStepSequence', () => {
  it('adds return step for round trip', () => {
    expect(buildStepSequence('return')).toEqual([
      'route',
      'date',
      'outbound',
      'return',
      'summary',
    ]);
  });

  it('skips return for single trip', () => {
    expect(buildStepSequence('single')).toEqual(['route', 'date', 'outbound', 'summary']);
  });
});

describe('buildStepLabels', () => {
  it('uses cfg strings when present', () => {
    const labels = buildStepLabels({ stepRoute: 'A', stepDate: 'B' });
    expect(labels.route).toBe('A');
    expect(labels.date).toBe('B');
  });
});
