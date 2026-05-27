import { unref } from 'vue';
import type { CalendarDayStatus, JourneyConnection, WizardStep } from '../types';
import type { WizardContext } from './useWizard';
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

export function applyWizardDebugPreset(wizard: WizardContext, debugKey: string): void {
  const presets = unref(wizard.cfg).debugPresets as Record<string, DebugPreset> | undefined;
  const preset = presets?.[debugKey];
  if (!preset) {
    return;
  }

  wizard.tripType.value = preset.tripType === 'return' ? 'return' : 'single';
  wizard.fromId.value = preset.from || 0;
  wizard.toId.value = preset.to || 0;
  wizard.fromTitle.value = preset.fromTitle || '';
  wizard.toTitle.value = preset.toTitle || '';
  wizard.dateYmd.value = preset.date || '';
  wizard.outbound.value = preset.outbound || null;
  wizard.inbound.value = preset.inbound || null;

  if (preset.calendarYear && preset.calendarMonth) {
    wizard.calYear.value = preset.calendarYear;
    wizard.calMonth.value = preset.calendarMonth;
  }
  if (preset.calendarDays) {
    wizard.debugCalendarDays.value = preset.calendarDays;
  }
  if (preset.outboundConnections?.length) {
    wizard.debugOutboundConnections.value = preset.outboundConnections;
  }
  if (preset.returnConnections?.length) {
    wizard.debugReturnConnections.value = preset.returnConnections;
  }

  wizard.goTo(preset.step || 'route');
}
