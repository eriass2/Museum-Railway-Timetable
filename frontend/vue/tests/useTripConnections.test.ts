import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createWizardStore } from '../src/wizard/store/createWizardStore';
import type { WizardVueConfig } from '../src/config/types';
import { useTripConnections } from '../src/wizard/composables/useTripConnections';
import type { JourneyConnection } from '../src/wizard/types';
import { clearTripConnectionsCache } from '../src/wizard/utils/tripConnectionsCache';

vi.mock('../src/api/mrtRest', () => ({
  mrtRestRequest: vi.fn(),
}));

import { mrtRestRequest } from '../src/api/mrtRest';

function wizardConfig(): WizardVueConfig {
  return {
    app: 'wizard',
    restUrl: 'http://example.test/wp-json/museum-railway-timetable/v1/',
    restNonce: 'nonce',
    stations: [
      { id: 1, title: 'Alpha' },
      { id: 2, title: 'Beta' },
    ],
    wizard: {
      errorGeneric: 'Något gick fel.',
    },
    labels: {},
  };
}

describe('useTripConnections', () => {
  beforeEach(() => {
    vi.mocked(mrtRestRequest).mockReset();
    clearTripConnectionsCache();
  });

  it('uses debug outbound connections without REST', async () => {
    const ctx = createWizardStore(wizardConfig());
    const mock: JourneyConnection[] = [{ service_id: 9, from_departure: '10:00', to_arrival: '11:00' }];
    ctx.store.debugOutboundConnections = mock;
    ctx.store.fromId = 1;
    ctx.store.toId = 2;
    ctx.store.dateYmd = '2026-06-01';

    const { loadConnections, connections } = useTripConnections(ctx, 'outbound');
    await loadConnections();

    expect(connections.value).toEqual(mock);
    expect(mrtRestRequest).not.toHaveBeenCalled();
  });

  it('loads connections from REST for outbound leg', async () => {
    vi.mocked(mrtRestRequest).mockResolvedValue({
      success: true,
      data: { connections: [{ service_id: 5, from_departure: '09:00', to_arrival: '10:00' }] },
    });

    const ctx = createWizardStore(wizardConfig());
    ctx.store.fromId = 1;
    ctx.store.toId = 2;
    ctx.store.dateYmd = '2026-06-01';

    const { loadConnections, connections } = useTripConnections(ctx, 'outbound');
    await loadConnections();

    expect(connections.value).toHaveLength(1);
    expect(mrtRestRequest).toHaveBeenCalledWith(
      expect.anything(),
      'mrt_search_journey',
      expect.objectContaining({
        from_station: 1,
        to_station: 2,
        date: '2026-06-01',
        trip_type: 'single',
      }),
    );
  });

  it('includes outbound arrival when loading return leg', async () => {
    vi.mocked(mrtRestRequest).mockResolvedValue({
      success: true,
      data: { connections: [] },
    });

    const ctx = createWizardStore(wizardConfig());
    ctx.store.fromId = 1;
    ctx.store.toId = 2;
    ctx.store.dateYmd = '2026-06-01';
    ctx.store.outbound = { service_id: 3, from_departure: '09:00', to_arrival: '10:30' };

    const { loadConnections } = useTripConnections(ctx, 'return');
    await loadConnections();

    expect(mrtRestRequest).toHaveBeenCalledWith(
      expect.anything(),
      'mrt_search_journey',
      expect.objectContaining({
        trip_type: 'return',
        outbound_arrival: '10:30',
      }),
    );
  });

  it('reuses client cache on repeat load without REST', async () => {
    vi.mocked(mrtRestRequest).mockResolvedValue({
      success: true,
      data: { connections: [{ service_id: 5, from_departure: '09:00', to_arrival: '10:00' }] },
    });

    const ctx = createWizardStore(wizardConfig());
    ctx.store.fromId = 1;
    ctx.store.toId = 2;
    ctx.store.dateYmd = '2026-06-01';

    const { loadConnections, connections } = useTripConnections(ctx, 'outbound');
    await loadConnections();
    await loadConnections();

    expect(connections.value).toHaveLength(1);
    expect(mrtRestRequest).toHaveBeenCalledTimes(1);
  });
});
