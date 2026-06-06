import { ref } from 'vue';
import { useMrtRest } from '../../composables/useMrtRest';
import type { JourneyConnection } from '../types';
import { cfgStr } from '../utils/wizardLabels';
import { arrivalAtDestination } from '../utils/connection';
import type { WizardInjection } from '../store/createWizardStore';
import {
  getTripConnectionsCache,
  setTripConnectionsCache,
  tripConnectionsCacheKey,
} from '../utils/tripConnectionsCache';

function resolveOutboundArrival(
  store: WizardInjection['store'],
  legCtx: 'outbound' | 'return',
): string {
  if (legCtx !== 'return' || !store.outbound) {
    return '';
  }
  return arrivalAtDestination(store.outbound);
}

export function useTripConnections(ctx: WizardInjection, legCtx: 'outbound' | 'return') {
  const { store, cfg, config } = ctx;
  const { loading, error, run } = useMrtRest(config);
  const connections = ref<JourneyConnection[]>([]);

  function cacheKey(): string {
    return tripConnectionsCacheKey(
      legCtx,
      store.fromId,
      store.toId,
      store.dateYmd,
      resolveOutboundArrival(store, legCtx),
    );
  }

  async function loadConnections(): Promise<void> {
    const mock =
      legCtx === 'outbound' ? store.debugOutboundConnections : store.debugReturnConnections;
    if (mock?.length) {
      connections.value = mock;
      return;
    }

    const key = cacheKey();
    const cached = getTripConnectionsCache(key);
    if (cached) {
      connections.value = cached;
      return;
    }

    const payload: Record<string, string | number> = {
      from_station: store.fromId,
      to_station: store.toId,
      date: store.dateYmd,
      trip_type: legCtx === 'return' ? 'return' : 'single',
    };

    if (legCtx === 'return' && store.outbound) {
      const arr = resolveOutboundArrival(store, legCtx);
      if (!arr) {
        store.showError(cfgStr(cfg, 'errorGeneric', 'Något gick fel. Försök igen.'));
        return;
      }
      payload.outbound_arrival = arr;
    }

    const res = await run<{ connections: JourneyConnection[] }>({
      method: 'POST',
      path: 'journey/search',
      body: payload,
    });
    if (res.success) {
      const list = res.data?.connections || [];
      connections.value = list;
      setTripConnectionsCache(key, list);
    }
  }

  return { loading, error, connections, loadConnections };
}
