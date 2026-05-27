import { computed, ref } from 'vue';
import type { MrtVueConfig } from '../../useMrtConfig';
import { msg } from '../../api/mrtApi';
import type { CalendarDayStatus, JourneyConnection, TripType, WizardStep } from '../types';
import { cfgStr, wizardCfg } from '../utils/wizardLabels';
import { formatYmdForDisplay } from '../utils/wizardDate';

export function useWizard(config: MrtVueConfig) {
  const cfg = computed(() => wizardStrings(config));
  const step = ref<WizardStep>('route');
  const fromId = ref(0);
  const toId = ref(0);
  const fromTitle = ref('');
  const toTitle = ref('');
  const tripType = ref<TripType>('single');
  const dateYmd = ref('');
  const calYear = ref(0);
  const calMonth = ref(0);
  const outbound = ref<JourneyConnection | null>(null);
  const inbound = ref<JourneyConnection | null>(null);
  const error = ref('');
  const debugCalendarDays = ref<Record<string, CalendarDayStatus> | null>(null);
  const debugOutboundConnections = ref<JourneyConnection[] | null>(null);
  const debugReturnConnections = ref<JourneyConnection[] | null>(null);

  const stepSequence = computed((): WizardStep[] => {
    const seq: WizardStep[] = ['route', 'date', 'outbound'];
    if (tripType.value === 'return') {
      seq.push('return');
    }
    seq.push('summary');
    return seq;
  });

  const stepLabels = computed(() => ({
    route: cfgStr(cfg.value, 'stepRoute', 'Sök resa'),
    date: cfgStr(cfg.value, 'stepDate', 'Datum'),
    outbound: cfgStr(cfg.value, 'stepOutbound', 'Utresa'),
    return: cfgStr(cfg.value, 'stepReturn', 'Återresa'),
    summary: cfgStr(cfg.value, 'stepSummary', 'Sammanfattning'),
  }));

  const contextLine = computed(() => {
    const trip =
      tripType.value === 'return'
        ? cfgStr(cfg.value, 'tripTypeReturn', 'Tur- och retur')
        : cfgStr(cfg.value, 'tripTypeSingle', 'Enkel');
    const route = `${fromTitle.value} → ${toTitle.value} | ${trip}`;
    if (!dateYmd.value) {
      return route;
    }
    const human = formatYmdForDisplay(dateYmd.value, cfg.value.monthNames as string[] | undefined);
    return `${route}\n${human}`;
  });

  function clearError(): void {
    error.value = '';
  }

  function showError(message: string): void {
    error.value = message;
  }

  function goTo(next: WizardStep): void {
    clearError();
    step.value = next;
  }

  function validateRoute(): boolean {
    if (!fromId.value || !toId.value) {
      showError(cfg.value.pleaseStations || 'Please select both stations.');
      return false;
    }
    if (fromId.value === toId.value) {
      showError(msg(config, 'errorSameStations', cfgStr(cfg.value, 'errorGeneric', 'Invalid stations.')));
      return false;
    }
    return true;
  }

  function setRoute(from: number, to: number, trip: TripType, fTitle: string, tTitle: string): void {
    fromId.value = from;
    toId.value = to;
    tripType.value = trip;
    fromTitle.value = fTitle;
    toTitle.value = tTitle;
    dateYmd.value = '';
    outbound.value = null;
    inbound.value = null;
  }

  function selectOutbound(conn: JourneyConnection): void {
    outbound.value = conn;
    inbound.value = null;
    if (tripType.value === 'return') {
      goTo('return');
    } else {
      goTo('summary');
    }
  }

  function selectInbound(conn: JourneyConnection): void {
    inbound.value = conn;
    goTo('summary');
  }

  return {
    config,
    cfg,
    step,
    fromId,
    toId,
    fromTitle,
    toTitle,
    tripType,
    dateYmd,
    calYear,
    calMonth,
    outbound,
    inbound,
    error,
    debugCalendarDays,
    debugOutboundConnections,
    debugReturnConnections,
    stepSequence,
    stepLabels,
    contextLine,
    clearError,
    showError,
    goTo,
    validateRoute,
    setRoute,
    selectOutbound,
    selectInbound,
  };
}

export type WizardContext = ReturnType<typeof useWizard>;
