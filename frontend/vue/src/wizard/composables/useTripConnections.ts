import { ref } from 'vue';
import { useMrtRest } from '../../composables/useMrtRest';
import type { JourneyConnection } from '../types';
import { journeySearchParams } from '../cache/cacheKeys';
import { cfgStr } from '../utils/wizardLabels';
import { arrivalAtDestination } from '../utils/connection';
import type { WizardInjection } from '../store/createWizardStore';

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
  const { store, cfg, config, resourceCache } = ctx;
  const { loading, error, run } = useMrtRest(config);
  const connections = ref<JourneyConnection[]>([]);

  async function loadConnections(): Promise<void> {
    const mock =
      legCtx === 'outbound' ? store.debugOutboundConnections : store.debugReturnConnections;
    if (mock?.length) {
      connections.value = mock;
      return;
    }

    const outboundArrival = resolveOutboundArrival(store, legCtx);
    if (legCtx === 'return' && store.outbound && !outboundArrival) {
      store.showError(cfgStr(cfg, 'errorGeneric', 'Något gick fel. Försök igen.'));
      return;
    }

    const params = journeySearchParams(
      legCtx,
      store.fromId,
      store.toId,
      store.dateYmd,
      outboundArrival,
    );

    const payload: Record<string, string | number> = {
      from_station: store.fromId,
      to_station: store.toId,
      date: store.dateYmd,
      trip_type: legCtx === 'return' ? 'return' : 'single',
    };
    if (legCtx === 'return' && outboundArrival) {
      payload.outbound_arrival = outboundArrival;
    }

    const list = await resourceCache.load(
      {
        resource: 'journey.search',
        params,
        request: async () => {
          const res = await run<{ connections: JourneyConnection[] }>({
            method: 'POST',
            path: 'journey/search',
            body: payload,
          });
          if (!res.success) {
            return null;
          }
          return res.data?.connections || [];
        },
      },
      { priority: 'user' },
    );

    if (list === null) {
      return;
    }
    connections.value = list;
  }

  return { loading, error, connections, loadConnections };
}
