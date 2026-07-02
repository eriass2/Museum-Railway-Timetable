import type { WizardVueConfig } from '../../config/types';
import { tripFootnotesFromStops } from '../../shared/stopTimeFootnotes';
import type { ConnectionLegContext } from '../composables/useConnectionLegDisplay';
import { loadConnectionDetailSegments } from '../composables/connectionDetailLoad';
import type { WizardStore } from '../store/wizardStoreTypes';
import type { JourneyConnection } from '../types';
import type { WizardCfg } from './wizardCfgTypes';
import type { TripSummaryTextInput } from './tripSummaryText';

type LegFootnoteParams = {
  config: WizardVueConfig;
  cfg: WizardCfg;
  connection: JourneyConnection;
  legCtx: ConnectionLegContext;
  fromId: number;
  toId: number;
  dateYmd: string;
};

/** Behovsuppehåll footnote texts for one connection (same rules as summary step). */
export async function loadTripSummaryLegFootnotes(params: LegFootnoteParams): Promise<string[]> {
  const legFrom = params.legCtx === 'return' ? params.toId : params.fromId;
  const legTo = params.legCtx === 'return' ? params.fromId : params.toId;
  const segments = await loadConnectionDetailSegments({
    config: params.config,
    cfg: params.cfg,
    connection: params.connection,
    legFrom,
    legTo,
    dateYmd: params.dateYmd,
  });
  if (!segments.length) {
    return [];
  }
  return tripFootnotesFromStops(
    segments.flatMap((segment) => segment.stops),
    params.cfg,
  ).map((entry) => entry.text);
}

/** Fetch connection-detail footnotes before PDF/plain export. */
export async function enrichTripSummaryInputWithFootnotes(
  input: TripSummaryTextInput,
  store: WizardStore,
  config: WizardVueConfig,
  cfg: WizardCfg,
): Promise<TripSummaryTextInput> {
  const links: { connection: JourneyConnection; legCtx: ConnectionLegContext }[] = [];
  if (store.outbound) {
    links.push({ connection: store.outbound, legCtx: 'outbound' });
  }
  if (store.tripType === 'return' && store.inbound) {
    links.push({ connection: store.inbound, legCtx: 'return' });
  }

  const legs = await Promise.all(
    input.legs.map(async (leg, index) => {
      const link = links[index];
      if (!link) {
        return leg;
      }
      const footnotes = await loadTripSummaryLegFootnotes({
        config,
        cfg,
        connection: link.connection,
        legCtx: link.legCtx,
        fromId: store.fromId,
        toId: store.toId,
        dateYmd: store.dateYmd,
      });
      if (footnotes.length === 0) {
        return leg;
      }
      return { ...leg, footnotes };
    }),
  );

  return { ...input, legs };
}
