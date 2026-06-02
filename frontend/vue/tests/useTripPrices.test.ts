import { computed, nextTick } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { useTripPrices } from '../src/composables/useTripPrices';

vi.mock('../src/api/mrtRest', () => ({
  mrtRestRequest: vi.fn(),
}));

import { mrtRestRequest } from '../src/api/mrtRest';

describe('useTripPrices', () => {
  const config = computed(() => ({
    restUrl: 'http://example.test/wp-json/museum-railway-timetable/v1/',
    restNonce: 'nonce',
  }));

  beforeEach(() => {
    vi.mocked(mrtRestRequest).mockReset();
  });

  it('skips REST when station ids are invalid', async () => {
    const query = computed(() => ({
      fromId: 0,
      toId: 0,
      tripType: 'single',
    }));
    const { trip, day, zones } = useTripPrices(config, query);
    await nextTick();
    expect(trip.value).toBeNull();
    expect(day.value).toBeNull();
    expect(zones.value).toBe(3);
    expect(mrtRestRequest).not.toHaveBeenCalled();
  });

  it('loads trip prices from REST', async () => {
    vi.mocked(mrtRestRequest).mockResolvedValue({
      success: true,
      data: {
        zones: 2,
        trip: {
          matrix: { return: { adult: 160 } },
          activeType: 'return',
          isAfternoonReturn: true,
        },
        day: null,
      },
    });

    const query = computed(() => ({
      fromId: 1,
      toId: 2,
      tripType: 'return',
      outboundDeparture: '15:00',
      inboundDeparture: '16:00',
    }));
    const { trip, zones, loading } = useTripPrices(config, query);

    await vi.waitFor(() => expect(loading.value).toBe(false));

    expect(mrtRestRequest).toHaveBeenCalledWith(
      config.value,
      'mrt_trip_prices',
      expect.objectContaining({
        from_id: 1,
        to_id: 2,
        trip_type: 'return',
        outbound_departure: '15:00',
        inbound_departure: '16:00',
        outbound_legs: '',
        inbound_legs: '',
      }),
    );
    expect(zones.value).toBe(2);
    expect(trip.value?.isAfternoonReturn).toBe(true);
  });

  it('clears prices when REST fails', async () => {
    vi.mocked(mrtRestRequest).mockResolvedValue({ success: false });

    const query = computed(() => ({
      fromId: 1,
      toId: 2,
      tripType: 'single',
    }));
    const { trip, day, loading } = useTripPrices(config, query);

    await vi.waitFor(() => expect(loading.value).toBe(false));

    expect(trip.value).toBeNull();
    expect(day.value).toBeNull();
  });
});
