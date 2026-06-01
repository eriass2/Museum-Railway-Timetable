import { describe, expect, it } from 'vitest';
import {
  ROAD_BUS_ICON_KEY,
  ROAD_BUS_TRAIN_TYPE_SLUG,
  normalizeTrainTypeIconKey,
  trainTypeIconUrl,
} from '../src/shared/trainTypeIcons';

describe('trainTypeIcons', () => {
  it('maps taxonomy slug buss to road bus icon key', () => {
    expect(normalizeTrainTypeIconKey('buss')).toBe('bus');
    expect(normalizeTrainTypeIconKey('Buss')).toBe('bus');
  });

  it('passes through icon keys unchanged', () => {
    expect(normalizeTrainTypeIconKey('railbus')).toBe('railbus');
    expect(normalizeTrainTypeIconKey('steam')).toBe('steam');
  });

  it('resolves slug or key when looking up icon URL', () => {
    const urls = { bus: '/bus.png', diesel: '/diesel.png', railbus: '/railbus.png' };
    expect(trainTypeIconUrl(urls, ROAD_BUS_TRAIN_TYPE_SLUG)).toBe('/bus.png');
    expect(trainTypeIconUrl(urls, ROAD_BUS_ICON_KEY)).toBe('/bus.png');
    expect(trainTypeIconUrl(urls, 'ralsbuss')).toBe('/railbus.png');
  });
});
