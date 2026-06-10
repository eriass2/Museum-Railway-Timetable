import { ref, watch, type ComputedRef, type Ref } from 'vue';
import type { MrtRestConfig } from '../config/types';
import { mrtRestRequest } from '../api/mrtRest';
import type { DayTicketData, TripPriceData } from '../shared/prices';

import type { TripPriceLeg } from '../shared/connectionPriceLegs';
import { encodeTripPriceLegs } from '../shared/connectionPriceLegs';

export type TripPricesQuery = {
  fromId: number;
  toId: number;
  tripType: string;
  outboundDeparture?: string;
  inboundDeparture?: string;
  includeDay?: boolean;
  outboundLegs?: TripPriceLeg[];
  inboundLegs?: TripPriceLeg[];
};

type TripPricesApiResponse = {
  zones: number;
  trip: TripPriceData | null;
  day: DayTicketData | null;
};

export function useTripPrices(
  config: ComputedRef<MrtRestConfig>,
  query: ComputedRef<TripPricesQuery>,
): {
  loading: Ref<boolean>;
  zones: Ref<number>;
  trip: Ref<TripPriceData | null>;
  day: Ref<DayTicketData | null>;
} {
  const loading = ref(false);
  const zones = ref(3);
  const trip = ref<TripPriceData | null>(null);
  const day = ref<DayTicketData | null>(null);

  watch(
    query,
    async (params) => {
      if (params.fromId <= 0 || params.toId <= 0) {
        zones.value = 3;
        trip.value = null;
        day.value = null;
        return;
      }

      loading.value = true;
      try {
        const response = await mrtRestRequest<TripPricesApiResponse>(config.value, {
          method: 'GET',
          path: 'prices/trip',
          query: {
            from_id: params.fromId,
            to_id: params.toId,
            trip_type: params.tripType,
            outbound_departure: params.outboundDeparture ?? '',
            inbound_departure: params.inboundDeparture ?? '',
            include_day: params.includeDay ? 1 : 0,
            outbound_legs: encodeTripPriceLegs(params.outboundLegs ?? []),
            inbound_legs: encodeTripPriceLegs(params.inboundLegs ?? []),
          },
        });
        if (!response.success || !response.data) {
          trip.value = null;
          day.value = null;
          return;
        }
        zones.value = response.data.zones;
        trip.value = response.data.trip;
        day.value = response.data.day;
      } finally {
        loading.value = false;
      }
    },
    { immediate: true },
  );

  return { loading, zones, trip, day };
}
