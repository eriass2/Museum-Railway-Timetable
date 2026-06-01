import { ref } from 'vue';
import { useMrtRest } from '../../composables/useMrtRest';
import type { JourneyConnection } from '../types';
import { cfgStr } from '../utils/wizardLabels';
import { arrivalAtDestination } from '../utils/connection';
import type { WizardInjection } from '../store/createWizardStore';

export function useTripConnections(ctx: WizardInjection, legCtx: 'outbound' | 'return') {
  const { store, cfg, config } = ctx;
  const { loading, error, run } = useMrtRest(config);
  const connections = ref<JourneyConnection[]>([]);

  async function loadConnections(): Promise<void> {
    connections.value = [];
    const mock =
      legCtx === 'outbound' ? store.debugOutboundConnections : store.debugReturnConnections;
    if (mock?.length) {
      connections.value = mock;
      return;
    }

    const payload: Record<string, string | number> = {
      from_station: store.fromId,
      to_station: store.toId,
      date: store.dateYmd,
      trip_type: legCtx === 'return' ? 'return' : 'single',
    };

    if (legCtx === 'return' && store.outbound) {
      const arr = arrivalAtDestination(store.outbound);
      if (!arr) {
        store.showError(cfgStr(cfg, 'errorGeneric', 'Något gick fel.'));
        return;
      }
      payload.outbound_arrival = arr;
    }

    const res = await run<{ connections: JourneyConnection[] }>(
      'mrt_search_journey',
      payload,
    );
    if (res.success) {
      connections.value = res.data?.connections || [];
    }
  }

  return { loading, error, connections, loadConnections };
}
