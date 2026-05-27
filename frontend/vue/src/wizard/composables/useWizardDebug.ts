import type { CalendarDayStatus, JourneyConnection, WizardStep } from '../types';
import type { WizardInjection } from '../store/createWizardStore';

type DebugPreset = {
  step?: WizardStep;
  tripType?: string;
  from?: number;
  to?: number;
  fromTitle?: string;
  toTitle?: string;
  date?: string;
  outbound?: JourneyConnection | null;
  inbound?: JourneyConnection | null;
  calendarDays?: Record<string, CalendarDayStatus>;
  calendarYear?: number;
  calendarMonth?: number;
  outboundConnections?: JourneyConnection[];
  returnConnections?: JourneyConnection[];
};

export function applyWizardDebugPreset(ctx: WizardInjection, debugKey: string): void {
  const { store, cfg } = ctx;
  const presets = cfg.value.debugPresets as Record<string, DebugPreset> | undefined;
  const preset = presets?.[debugKey];
  if (!preset) {
    return;
  }

  store.tripType = preset.tripType === 'return' ? 'return' : 'single';
  store.fromId = preset.from || 0;
  store.toId = preset.to || 0;
  store.fromTitle = preset.fromTitle || '';
  store.toTitle = preset.toTitle || '';
  store.dateYmd = preset.date || '';
  store.outbound = preset.outbound || null;
  store.inbound = preset.inbound || null;

  if (preset.calendarYear && preset.calendarMonth) {
    store.calYear = preset.calendarYear;
    store.calMonth = preset.calendarMonth;
  }
  if (preset.calendarDays) {
    store.debugCalendarDays = preset.calendarDays;
  }
  if (preset.outboundConnections?.length) {
    store.debugOutboundConnections = preset.outboundConnections;
  }
  if (preset.returnConnections?.length) {
    store.debugReturnConnections = preset.returnConnections;
  }

  store.goTo(preset.step || 'route');
}
