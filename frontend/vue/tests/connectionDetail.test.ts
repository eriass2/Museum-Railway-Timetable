import { computed, ref } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { loadConnectionDetailSegments } from '../src/wizard/composables/connectionDetailLoad';
import { useConnectionDetail } from '../src/wizard/composables/useConnectionDetail';
import type { WizardVueConfig } from '../src/config/types';
import type { JourneyConnection } from '../src/shared/journey';

vi.mock('../src/api/mrtRest', () => ({
  mrtRestRequest: vi.fn(),
}));

import { mrtRestRequest } from '../src/api/mrtRest';

const config: WizardVueConfig = {
  app: 'wizard',
  restUrl: 'http://example.test/wp-json/museum-railway-timetable/v1/',
  restNonce: 'nonce',
  stations: [
    { id: 1, title: 'Alpha' },
    { id: 2, title: 'Beta' },
    { id: 3, title: 'Gamma' },
  ],
  wizard: {
    errorGeneric: 'Något gick fel.',
    changeAt: 'Byte vid %s',
    transferTrip: 'Byte',
    legSegmentLabel: 'Delsträcka %d',
  },
  labels: {},
};

const directConnection: JourneyConnection = {
  service_id: 501,
  from_departure: '09:00',
  to_arrival: '10:00',
};

describe('connectionDetailLoad', () => {
  beforeEach(() => {
    vi.mocked(mrtRestRequest).mockReset();
  });

  it('loadConnectionDetailSegments maps REST detail for direct trip', async () => {
    vi.mocked(mrtRestRequest).mockResolvedValue({
      success: true,
      data: {
        detail: {
          stops: [{ station_title: 'Alpha', departure_time: '09:00' }],
        },
        notice: 'Ersatt lok',
      },
    });

    const segments = await loadConnectionDetailSegments({
      config,
      cfg: { legSegmentLabel: 'Delsträcka %d', errorGeneric: 'Fel' },
      connection: directConnection,
      legFrom: 1,
      legTo: 2,
    });

    expect(segments).toHaveLength(1);
    expect(segments[0]?.notice).toBe('Ersatt lok');
    expect(segments[0]?.stops).toHaveLength(1);
  });

  it('returns empty when REST detail fails', async () => {
    vi.mocked(mrtRestRequest).mockResolvedValue({ success: false });

    const segments = await loadConnectionDetailSegments({
      config,
      cfg: { legSegmentLabel: 'Delsträcka %d', errorGeneric: 'Fel' },
      connection: directConnection,
      legFrom: 1,
      legTo: 2,
    });

    expect(segments).toEqual([]);
  });
});

describe('useConnectionDetail', () => {
  beforeEach(() => {
    vi.mocked(mrtRestRequest).mockReset();
  });

  it('loads segments once and exposes transfer labels', async () => {
    vi.mocked(mrtRestRequest).mockResolvedValue({
      success: true,
      data: {
        detail: { stops: [] },
        notice: '',
      },
    });

    const connection: JourneyConnection = {
      service_id: 0,
      from_departure: '09:00',
      to_arrival: '11:00',
      legs: [
        {
          service_id: 1,
          from_station_id: 1,
          to_station_id: 3,
          from_departure: '09:00',
          to_arrival: '10:00',
        },
        {
          service_id: 2,
          from_station_id: 3,
          to_station_id: 2,
          from_departure: '10:15',
          to_arrival: '11:00',
        },
      ],
    };

    const detail = useConnectionDetail({
      config,
      cfg: computed(() => ({
        errorGeneric: 'Fel',
        changeAt: 'Byte vid %s',
        transferTrip: 'Byte',
        legSegmentLabel: 'Delsträcka %d',
      })),
      connection,
      legFrom: ref(1),
      legTo: ref(2),
    });

    await detail.ensureLoaded();

    expect(detail.loaded.value).toBe(true);
    expect(detail.segments.value.length).toBeGreaterThan(0);
    expect(detail.transferLabelAt(0)).toContain('Gamma');
  });
});
