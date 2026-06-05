import { computed, toValue, type MaybeRefOrGetter } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import type { ConnectionLegSummaryItem } from '../../shared/connectionLegDisplay';
import { stationTitleLookup } from '../../shared/connectionLegDisplay';
import { buildConnectionLegSummary } from '../utils/buildConnectionLegSummary';
import { arrivalAtDestination, departureFromOrigin } from '../utils/connection';
import { formatTripClock } from '../utils/format';
import type { JourneyConnection } from '../types';

export type ConnectionLegContext = 'outbound' | 'return';

export function connectionRouteText(
  legCtx: ConnectionLegContext,
  fromTitle: string,
  toTitle: string,
): string {
  return legCtx === 'return' ? `${toTitle} → ${fromTitle}` : `${fromTitle} → ${toTitle}`;
}

export function connectionTimeRange(conn: JourneyConnection): string {
  return `${formatTripClock(departureFromOrigin(conn))} – ${formatTripClock(arrivalAtDestination(conn))}`;
}

export function connectionLegItems(
  conn: JourneyConnection,
  stations: { id: number; title: string }[],
  cfg: Parameters<typeof buildConnectionLegSummary>[2],
): ConnectionLegSummaryItem[] {
  return buildConnectionLegSummary(conn, stationTitleLookup(stations), cfg);
}

export function useConnectionLegDisplay(
  connection: MaybeRefOrGetter<JourneyConnection | null | undefined>,
  legCtx: ConnectionLegContext,
) {
  const { store, cfg } = useWizardContext();

  const routeText = computed(() =>
    connectionRouteText(legCtx, store.fromTitle, store.toTitle),
  );

  const timeRange = computed(() => {
    const conn = toValue(connection);
    return conn ? connectionTimeRange(conn) : '';
  });

  const legItems = computed(() => {
    const conn = toValue(connection);
    if (!conn) {
      return [];
    }
    return connectionLegItems(conn, store.config.stations || [], cfg.value);
  });

  return { routeText, timeRange, legItems };
}
