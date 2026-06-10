import { describe, expect, it } from 'vitest';
import {
  formatDeviationPlanned,
  formatTrainConnecting,
  overviewUiLabels,
} from '../src/shared/overviewUiLabels';

describe('overviewUiLabels', () => {
  it('reads PHP strings when present', () => {
    const labels = overviewUiLabels({
      strings: { ovPrintKeyTitle: 'Legend' },
    });
    expect(labels.printKeyTitle).toBe('Legend');
  });

  it('uses Swedish fallbacks when strings missing', () => {
    expect(overviewUiLabels({}).printKeyTitle).toBe('Förklaringar');
  });
});

describe('overview label formatters', () => {
  it('formats deviation and train connecting templates', () => {
    expect(formatDeviationPlanned('Planerat: %s', 'Ångtåg')).toBe('Planerat: Ångtåg');
    expect(formatTrainConnecting('Tåg %1$s %2$s', '71', '10:00')).toBe('Tåg 71 10:00');
  });
});
