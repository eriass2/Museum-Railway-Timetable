import { describe, expect, it } from 'vitest';
import { shouldShowConnectionLegList } from '../src/shared/connectionLegDisplay';

const leg = {
  type: 'leg' as const,
  leg: {
    vehicleLabel: 'Rälsbuss 101',
    iconUrl: '',
    kind: 'train',
    timeRange: '16.45 – 17.04',
    route: 'A → B',
  },
};

describe('shouldShowConnectionLegList', () => {
  it('hides a single direct leg', () => {
    expect(shouldShowConnectionLegList([leg])).toBe(false);
  });

  it('shows multiple legs or transfers', () => {
    expect(shouldShowConnectionLegList([leg, leg])).toBe(true);
    expect(shouldShowConnectionLegList([leg, { type: 'transfer', label: 'Byte' }])).toBe(true);
  });
});
