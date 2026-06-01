import { describe, expect, it } from 'vitest';
import { trainIconKey, legVehicleLabel, legToVehicleItem } from '../src/wizard/utils/vehicle';

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
        service_id: 101,
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
        { train_type: 'Rälsbuss', service_id: 101, service_number: '101', destination: 'Faringe' },
        cfg,
      ),
    ).toBe('Rälsbuss 101 towards Faringe');
  });
});

describe('legToVehicleItem', () => {
  it('maps leg fields to vehicle row item', () => {
    const cfg = { trainTypeSlugIcons: { buss: 'bus' }, trainTypeIcons: { bus: '/bus.svg' } };
    const item = legToVehicleItem(
      {
        service_id: 1,
        train_type: 'Buss',
        train_type_slug: 'buss',
        service_number: 'B1',
        destination: 'Uppsala Östra',
      },
      cfg,
    );
    expect(item.label).toBe('Buss B1 mot Uppsala Östra');
    expect(item.kind).toBe('bus');
    expect(item.iconUrl).toBe('/bus.svg');
  });
});
