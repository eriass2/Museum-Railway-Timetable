import { computed } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import { useTripPrices } from '../../composables/useTripPrices';
import { connectionToPriceLegs } from '../../shared/connectionPriceLegs';
import {
  afternoonClockFromPriceNote,
  resolveTicketCopyFootnotes,
} from '../../shared/ticketCopy';
import { priceTableLabelsFromCfg } from '../utils/priceTableLabels';
import { departureFromOrigin } from '../utils/connection';
export function useSummaryPrices() {
  const { store, cfg, config } = useWizardContext();

  const tripPricesQuery = computed(() => ({
    fromId: store.fromId,
    toId: store.toId,
    tripType: store.tripType,
    outboundDeparture: store.outbound ? departureFromOrigin(store.outbound) : '',
    inboundDeparture: store.inbound ? departureFromOrigin(store.inbound) : '',
    includeDay: true,
    outboundLegs: store.outbound
      ? connectionToPriceLegs(store.outbound, store.fromId, store.toId)
      : [],
    inboundLegs:
      store.tripType === 'return' && store.inbound
        ? connectionToPriceLegs(store.inbound, store.toId, store.fromId)
        : [],
  }));

  const restConfig = computed(() => config);

  const { loading: pricesLoading, zones, trip: priceData, day: dayPrices } = useTripPrices(
    restConfig,
    tripPricesQuery,
  );

  const stationPurchaseNote = computed(() => {
    const map = config.ticketPurchaseByStation ?? {};
    return (map[String(store.fromId)] ?? '').trim();
  });

  const ticketCopyFootnotes = computed(() =>
    resolveTicketCopyFootnotes(config.ticketCopyNotes ?? [], {
      isAfternoon: !!priceData.value?.isAfternoonReturn,
      hasDayTicket: !!dayPrices.value?.day,
      afternoonClock: afternoonClockFromPriceNote(cfg.value.priceAfternoonNote),
    }),
  );

  const priceLabels = computed(() => ({
    ...priceTableLabelsFromCfg(
      cfg.value,
      zones.value,
      store.tripType === 'return' || !priceData.value?.isAfternoonReturn,
    ),
    stationPurchaseNote: stationPurchaseNote.value,
    footnotes: ticketCopyFootnotes.value,
  }));

  return {
    pricesLoading,
    zones,
    priceData,
    dayPrices,
    priceLabels,
  };
}
