import type { WizardCfg } from '../wizard/utils/wizardCfgTypes';
import { cfgStr } from '../wizard/utils/wizardLabels';
import type { BehovHint, TimelineStopBase } from './timelineStop';

export type StopTimeFootnoteStop = TimelineStopBase;

/** Short accessible label for behovsuppehåll info icons (UL-style circle-i, not emoji). */
export const ON_REQUEST_INFO_ARIA_LABEL = 'Behovsuppehåll';

export type TripFootnoteEntry = {
  text: string;
};

function normalizeBehovHint(stop: StopTimeFootnoteStop): BehovHint {
  return stop.behov_hint ?? '';
}

/** Whether a stop should show the behovsuppehåll info icon in the timeline. */
export function stopShowsOnRequestInfo(stop: StopTimeFootnoteStop): boolean {
  const hint = normalizeBehovHint(stop);
  return hint === 'pickup' || hint === 'dropoff' || hint === 'both';
}

function footnoteTextForStop(stop: StopTimeFootnoteStop, cfg: WizardCfg): string | null {
  const hint = normalizeBehovHint(stop);
  if (hint === 'pickup') {
    const text = cfgStr(cfg, 'onRequestPickupFootnote', '');
    return text !== '' ? text : null;
  }
  if (hint === 'dropoff' || hint === 'both') {
    const text = cfgStr(cfg, 'onRequestDropoffFootnote', '');
    return text !== '' ? text : null;
  }
  return null;
}

/** One footnote per unique text, from passenger-relevant stops only (API filters endpoints). */
export function tripFootnotesFromStops(
  stops: StopTimeFootnoteStop[],
  cfg: WizardCfg,
): TripFootnoteEntry[] {
  const entries: TripFootnoteEntry[] = [];
  const seen = new Set<string>();

  for (const stop of stops) {
    const text = footnoteTextForStop(stop, cfg);
    if (text === null || seen.has(text)) {
      continue;
    }
    seen.add(text);
    entries.push({ text });
  }

  return entries;
}
