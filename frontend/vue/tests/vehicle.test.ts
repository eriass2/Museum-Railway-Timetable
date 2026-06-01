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
  it('combines train type and service number', () => {
    expect(
      legVehicleLabel({ train_type: 'Ångtåg', service_number: '42', service_id: 1 }),
    ).toBe('Ångtåg 42');
  });

  it('prefers service number over long service title', () => {
    expect(
      legVehicleLabel({
        train_type: 'Rälsbuss',
        service_name: 'Uppsala Östra – Faringe 101',
        service_number: '101',
        destination: 'Faringe',
      }),
    ).toBe('Rälsbuss 101 mot Faringe');
  });

  it('uses localized towards template when cfg provided', () => {
    const cfg = { towards: 'towards %s' };
    expect(
      legVehicleLabel(
        { train_type: 'Rälsbuss', service_number: '101', destination: 'Faringe' },
        cfg,
      ),
    ).toBe('Rälsbuss 101 towards Faringe');
  });
});
