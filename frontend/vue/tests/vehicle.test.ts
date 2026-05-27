import { describe, expect, it } from 'vitest';
import { trainIconKey, legVehicleLabel } from '../src/wizard/utils/vehicle';

describe('trainIconKey', () => {
  it('uses slug map when present', () => {
    const cfg = { trainTypeSlugIcons: { angtag: 'steam' } };
    expect(trainIconKey('', 'angtag', '', cfg)).toBe('steam');
  });

  it('detects railbus from label', () => {
    expect(trainIconKey('Rälsbuss 2', '', '', {})).toBe('railbus');
  });
});

describe('legVehicleLabel', () => {
  it('combines train type and service', () => {
    expect(
      legVehicleLabel({ train_type: 'Ångtåg', service_number: '42', service_id: 1 }),
    ).toBe('Ångtåg 42');
  });
});
