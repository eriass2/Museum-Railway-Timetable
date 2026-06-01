import type { ComputedRef } from 'vue';
import type { WizardVueConfig } from '../../config/types';
import type { CalendarDayInfo, CalendarDayStatus, JourneyConnection, TripType, WizardStep } from '../types';
import type { WizardCfg } from '../utils/wizardLabels';

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
  debugCalendarDays: Record<string, CalendarDayInfo | CalendarDayStatus> | null;
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
