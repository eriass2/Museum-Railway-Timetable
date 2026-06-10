import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { ref } from 'vue';
import { useStopTimes } from '../src/admin/composables/timetable-editor/useStopTimes';
import type { AdminClientConfig } from '../src/admin/types';

vi.mock('../src/admin/api/adminRest', () => ({
  getStopTimes: vi.fn(),
  saveStopTimes: vi.fn(),
}));

import { getStopTimes, saveStopTimes } from '../src/admin/api/adminRest';

describe('useStopTimes', () => {
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
        stopTimesSaved: 'Stopptider sparade',
        saveFailed: 'Kunde inte spara',
        genericError: 'Något gick fel',
      },
    };
    vi.mocked(getStopTimes).mockReset();
    vi.mocked(saveStopTimes).mockReset();
  });

  afterEach(() => {
    delete adminWindow.mrtAdminVue;
    vi.unstubAllGlobals();
  });

  it('load fetches stop times for service id', async () => {
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

    const serviceId = ref(501);
    const { stations, loading, load } = useStopTimes(serviceId);
    await load();

    expect(loading.value).toBe(false);
    expect(stations.value).toHaveLength(1);
    expect(stations.value[0]?.name).toBe('Alpha');
  });

  it('save posts payload and updates stations', async () => {
    const rows = [
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
    ];
    vi.mocked(saveStopTimes).mockResolvedValue({ route_id: 50, stations: rows });

    const serviceId = ref(501);
    const { stations, save, message } = useStopTimes(serviceId);
    stations.value = rows;
    await save();

    expect(saveStopTimes).toHaveBeenCalledWith(
      501,
      expect.arrayContaining([
        expect.objectContaining({ station_id: 101, departure: '09:15' }),
      ]),
      false,
    );
    expect(message.value).toBe('Stopptider sparade');
  });

  it('persistRows uses quick-edit flag', async () => {
    vi.mocked(saveStopTimes).mockResolvedValue({ route_id: 50, stations: [] });

    const serviceId = ref(501);
    const { persistRows } = useStopTimes(serviceId);
    await persistRows(501, [], true);

    expect(saveStopTimes).toHaveBeenCalledWith(501, [], true);
  });
});
