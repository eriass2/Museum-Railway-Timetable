import type { WizardCfg } from '../wizard/utils/wizardCfgTypes';
import { cfgStr } from '../wizard/utils/wizardLabels';

export type StopTimeFootnoteStop = {
  on_request_pickup?: boolean;
  on_request_dropoff?: boolean;
  on_request_both?: boolean;
  approximate_time?: boolean;
};

export type FootnoteMark = 'P' | 'A';

export type TripFootnoteEntry = {
  mark: FootnoteMark;
  text: string;
};

export function segmentNeedsPickupFootnote(stops: StopTimeFootnoteStop[]): boolean {
  return stops.some((s) => s.on_request_pickup || s.on_request_both);
}

export function segmentNeedsDropoffFootnote(stops: StopTimeFootnoteStop[]): boolean {
  return stops.some((s) => s.on_request_dropoff || s.on_request_both);
}

/** Superscript markers shown next to station names in the timeline. */
export function footnoteMarksForStop(stop: StopTimeFootnoteStop): FootnoteMark[] {
  const marks: FootnoteMark[] = [];
  if (stop.on_request_pickup || stop.on_request_both) {
    marks.push('P');
  }
  if (stop.on_request_dropoff || stop.on_request_both) {
    marks.push('A');
  }
  return marks;
}

/** One footnote per mark (P/A), deduplicated across all stops in the trip. */
export function tripFootnotesFromStops(
  stops: StopTimeFootnoteStop[],
  cfg: WizardCfg,
): TripFootnoteEntry[] {
  const entries: TripFootnoteEntry[] = [];
  const seen = new Set<FootnoteMark>();

  for (const stop of stops) {
    for (const mark of footnoteMarksForStop(stop)) {
      if (seen.has(mark)) {
        continue;
      }
      seen.add(mark);
      const text =
        mark === 'P'
          ? cfgStr(cfg, 'onRequestPickupFootnote', '')
          : cfgStr(cfg, 'onRequestDropoffFootnote', '');
      if (text !== '') {
        entries.push({ mark, text });
      }
    }
  }

  return entries;
}

/** @deprecated Per-segment footnotes; use tripFootnotesFromStops at trip level. */
export function stopTimeFootnotesForSegment(
  stops: StopTimeFootnoteStop[],
  cfg: WizardCfg,
): string[] {
  return tripFootnotesFromStops(stops, cfg).map((e) => e.text);
}
