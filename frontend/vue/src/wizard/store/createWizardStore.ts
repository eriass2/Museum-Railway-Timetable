import { computed, reactive, type ComputedRef } from 'vue';
import type { WizardVueConfig } from '../../config/types';
import type { CalendarDayStatus, JourneyConnection, TripType, WizardStep } from '../types';
import { cfgStr, wizardCfg, type WizardCfg } from '../utils/wizardLabels';
import { formatYmdForDisplay } from '../utils/wizardDate';
import { buildStepLabels, buildStepSequence } from './wizardSteps';
import { resetRouteSelections, validateWizardRoute } from './wizardRoute';
import { applyInboundSelection, applyOutboundSelection } from './wizardSelections';

export type WizardStore = {
  config: WizardVueConfig;
  step: WizardStep;
  fromId: number;
  toId: number;
  fromTitle: string;
  toTitle: string;
  tripType: TripType;
  dateYmd: string;
  calYear: number;
  calMonth: number;
  outbound: JourneyConnection | null;
  inbound: JourneyConnection | null;
  error: string;
  debugCalendarDays: Record<string, CalendarDayStatus> | null;
  debugOutboundConnections: JourneyConnection[] | null;
  debugReturnConnections: JourneyConnection[] | null;
  readonly stepSequence: WizardStep[];
  readonly stepLabels: Record<WizardStep, string>;
  readonly contextLine: string;
  clearError: () => void;
  showError: (message: string) => void;
  goTo: (next: WizardStep) => void;
  validateRoute: (fromId?: number, toId?: number) => boolean;
  setRoute: (
    from: number,
    to: number,
    trip: TripType,
    fTitle: string,
    tTitle: string,
  ) => void;
  selectOutbound: (conn: JourneyConnection) => void;
  selectInbound: (conn: JourneyConnection) => void;
};

export type WizardInjection = {
  config: WizardVueConfig;
  cfg: ComputedRef<WizardCfg>;
  store: WizardStore;
};

export function createWizardStore(config: WizardVueConfig): WizardInjection {
  const cfg = computed((): WizardCfg => wizardCfg(config));

  const store = reactive({
    config,
    step: 'route' as WizardStep,
    fromId: 0,
    toId: 0,
    fromTitle: '',
    toTitle: '',
    tripType: 'single' as TripType,
    dateYmd: '',
    calYear: 0,
    calMonth: 0,
    outbound: null as JourneyConnection | null,
    inbound: null as JourneyConnection | null,
    error: '',
    debugCalendarDays: null as Record<string, CalendarDayStatus> | null,
    debugOutboundConnections: null as JourneyConnection[] | null,
    debugReturnConnections: null as JourneyConnection[] | null,

    get stepSequence(): WizardStep[] {
      return buildStepSequence(this.tripType);
    },
    get stepLabels(): Record<WizardStep, string> {
      return buildStepLabels(cfg.value);
    },
    get contextLine(): string {
      const trip =
        this.tripType === 'return'
          ? cfgStr(cfg.value, 'tripTypeReturn', 'Tur- och retur')
          : cfgStr(cfg.value, 'tripTypeSingle', 'Enkel');
      const route = `${this.fromTitle} → ${this.toTitle} | ${trip}`;
      if (!this.dateYmd) {
        return route;
      }
      const human = formatYmdForDisplay(
        this.dateYmd,
        cfg.value.monthNames as string[] | undefined,
      );
      return `${route}\n${human}`;
    },

    clearError(): void {
      this.error = '';
    },
    showError(message: string): void {
      this.error = message;
    },
    goTo(next: WizardStep): void {
      this.clearError();
      this.step = next;
    },
    validateRoute(fromId?: number, toId?: number): boolean {
      return validateWizardRoute(
        this,
        config,
        cfg.value,
        fromId ?? this.fromId,
        toId ?? this.toId,
      );
    },
    setRoute(
      from: number,
      to: number,
      trip: TripType,
      fTitle: string,
      tTitle: string,
    ): void {
      this.fromId = from;
      this.toId = to;
      this.tripType = trip;
      this.fromTitle = fTitle;
      this.toTitle = tTitle;
      resetRouteSelections(this);
    },
    selectOutbound(conn: JourneyConnection): void {
      applyOutboundSelection(this, conn);
    },
    selectInbound(conn: JourneyConnection): void {
      applyInboundSelection(this, conn);
    },
  }) as WizardStore;

  return { config, cfg, store };
}
