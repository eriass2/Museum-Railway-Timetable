import type { WizardCfg } from '../wizard/utils/wizardCfgTypes';
import { cfgStr } from '../wizard/utils/wizardLabels';

export type StopTimeFootnoteStop = {
  on_request_pickup?: boolean;
  on_request_dropoff?: boolean;
  on_request_both?: boolean;
  approximate_time?: boolean;
};

export const ON_REQUEST_INFO_MARK = 'ℹ️';

export type TripFootnoteEntry = {
  mark: typeof ON_REQUEST_INFO_MARK;
  text: string;
};

/** Whether a stop should show the behovsuppehåll info icon in the timeline. */
export function stopShowsOnRequestInfo(stop: StopTimeFootnoteStop): boolean {
  return Boolean(stop.on_request_pickup || stop.on_request_dropoff);
}

function footnoteTextForStop(stop: StopTimeFootnoteStop, cfg: WizardCfg): string | null {
  if (stop.on_request_pickup) {
    const text = cfgStr(cfg, 'onRequestPickupFootnote', '');
    return text !== '' ? text : null;
  }
  if (stop.on_request_dropoff || stop.on_request_both) {
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
    entries.push({ mark: ON_REQUEST_INFO_MARK, text });
  }

  return entries;
}
