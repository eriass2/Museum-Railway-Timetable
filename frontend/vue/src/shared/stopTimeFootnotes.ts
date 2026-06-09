import type { WizardCfg } from '../wizard/utils/wizardCfgTypes';
import { cfgStr } from '../wizard/utils/wizardLabels';

export type StopTimeFootnoteStop = {
  on_request_pickup?: boolean;
  on_request_dropoff?: boolean;
  on_request_both?: boolean;
  approximate_time?: boolean;
};

export function segmentNeedsPickupFootnote(stops: StopTimeFootnoteStop[]): boolean {
  return stops.some((s) => s.on_request_pickup || s.on_request_both);
}

export function segmentNeedsDropoffFootnote(stops: StopTimeFootnoteStop[]): boolean {
  return stops.some((s) => s.on_request_dropoff || s.on_request_both);
}

export function stopTimeFootnotesForSegment(
  stops: StopTimeFootnoteStop[],
  cfg: WizardCfg,
): string[] {
  const notes: string[] = [];
  if (segmentNeedsPickupFootnote(stops)) {
    notes.push(cfgStr(cfg, 'onRequestPickupFootnote', ''));
  }
  if (segmentNeedsDropoffFootnote(stops)) {
    notes.push(cfgStr(cfg, 'onRequestDropoffFootnote', ''));
  }
  return notes.filter((n) => n !== '');
}
