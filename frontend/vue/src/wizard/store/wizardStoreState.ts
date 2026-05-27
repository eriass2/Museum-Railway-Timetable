import { type ComputedRef, reactive } from 'vue';
import type { WizardVueConfig } from '../../config/types';
import type { CalendarDayStatus, JourneyConnection, TripType, WizardStep } from '../types';
import type { WizardCfg } from '../utils/wizardLabels';
import { resetRouteSelections, validateWizardRoute, goToStep } from './wizardRoute';
import { applyInboundSelection, applyOutboundSelection } from './wizardSelections';
import {
  wizardContextLine,
  wizardStepLabels,
  wizardStepSequence,
} from './wizardStoreGetters';
import type { WizardStore } from './wizardStoreTypes';

export function buildWizardStoreState(
  config: WizardVueConfig,
  cfg: ComputedRef<WizardCfg>,
): WizardStore {
  return reactive({
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
      return wizardStepSequence(this.tripType);
    },
    get stepLabels(): Record<WizardStep, string> {
      return wizardStepLabels(cfg.value);
    },
    get contextLine(): string {
      return wizardContextLine(this, cfg.value);
    },

    clearError(): void {
      this.error = '';
    },
    showError(message: string): void {
      this.error = message;
    },
    goTo(next: WizardStep): void {
      goToStep(this, next);
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
}
