import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { useOverviewGridEdit } from '../src/admin/composables/timetable-editor/useOverviewGridEdit';
import type { AdminClientConfig } from '../src/admin/types';

vi.mock('../src/admin/api/adminRest', () => ({
  getStopTimes: vi.fn(),
  saveStopTimes: vi.fn(),
}));

import { getStopTimes, saveStopTimes } from '../src/admin/api/adminRest';

describe('useOverviewGridEdit', () => {
  const adminWindow: { mrtAdminVue?: AdminClientConfig } = {};

  beforeEach(() => {
    vi.stubGlobal('window', adminWindow);
    adminWindow.mrtAdminVue = {
      restUrl: 'https://example.test/wp-json/museum-railway-timetable/v1',
      restNonce: 'nonce',
      initialRoute: '/dashboard',
      adminBase: 'https://example.test/wp-admin/admin.php?page=mrt_app',
      canManage: true,
      canOperate: true,
      isDevMode: false,
      trainTypeIconUrls: {},
      strings: {
        stopTimesSaved: 'Sparat',
        saveFailed: 'Kunde inte spara',
      },
    };
    vi.mocked(getStopTimes).mockReset();
    vi.mocked(saveStopTimes).mockReset();
  });

  afterEach(() => {
    delete adminWindow.mrtAdminVue;
    vi.unstubAllGlobals();
  });

  it('normalizes hh:mm for input fields', () => {
    const { hhmmToInput, inputToHhmm } = useOverviewGridEdit();
    expect(hhmmToInput('9:05')).toBe('09:05');
    expect(hhmmToInput('bad')).toBe('');
    expect(inputToHhmm('9:05')).toBe('09:05');
    expect(inputToHhmm('')).toBe('');
  });

  it('mergeEdit applies patch over existing cell', () => {
    const { mergeEdit } = useOverviewGridEdit();
    const merged = mergeEdit(1, 2, { arrival: '09:00', departure: '', stopsHere: true, pickupAllowed: true, dropoffAllowed: true, approximateTime: false }, {
      departure: '09:05',
    });
    expect(merged.departure).toBe('09:05');
    expect(merged.arrival).toBe('09:00');
  });

  it('applyCellEdit loads, updates and quick-saves stop times', async () => {
    vi.mocked(getStopTimes).mockResolvedValue({
      route_id: 50,
      stations: [
        {
          id: 101,
          name: 'Alpha',
          sequence: 1,
          stops_here: true,
          arrival_time: '',
          departure_time: '09:00',
          pickup_allowed: true,
          dropoff_allowed: true,
          approximate_time: false,
        },
      ],
    });
    vi.mocked(saveStopTimes).mockResolvedValue({
      route_id: 50,
      stations: [
        {
          id: 101,
          name: 'Alpha',
          sequence: 1,
          stops_here: true,
          arrival_time: '',
          departure_time: '09:15',
          pickup_allowed: true,
          dropoff_allowed: true,
          approximate_time: false,
        },
      ],
    });

    const { applyCellEdit, message } = useOverviewGridEdit();
    await applyCellEdit(501, 101, {
      arrival: '',
      departure: '09:15',
      stopsHere: true,
      pickupAllowed: true,
      dropoffAllowed: true,
      approximateTime: false,
    });

    expect(getStopTimes).toHaveBeenCalledWith(501);
    expect(saveStopTimes).toHaveBeenCalledWith(
      501,
      expect.arrayContaining([
        expect.objectContaining({ station_id: 101, departure: '09:15' }),
      ]),
      true,
    );
    expect(message.value).toBe('Sparat');
  });

  it('clearCache forces reload on next edit', async () => {
    vi.mocked(getStopTimes).mockResolvedValue({
      route_id: 50,
      stations: [
        {
          id: 101,
          name: 'Alpha',
          sequence: 1,
          stops_here: true,
          arrival_time: '',
          departure_time: '09:00',
          pickup_allowed: true,
          dropoff_allowed: true,
          approximate_time: false,
        },
      ],
    });
    vi.mocked(saveStopTimes).mockResolvedValue({
      route_id: 50,
      stations: [],
    });
    const edit = {
      arrival: '',
      departure: '09:15',
      stopsHere: true,
      pickupAllowed: true,
      dropoffAllowed: true,
      approximateTime: false,
    };
    const { applyCellEdit, clearCache } = useOverviewGridEdit();
    await applyCellEdit(501, 101, edit);
    expect(getStopTimes).toHaveBeenCalledTimes(1);
    clearCache();
    await applyCellEdit(501, 101, edit);
    expect(getStopTimes).toHaveBeenCalledTimes(2);
  });
});
