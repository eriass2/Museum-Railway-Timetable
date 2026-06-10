import { describe, expect, it } from 'vitest';
import {
  footnoteMarksForStop,
  stopTimeFootnotesForSegment,
  tripFootnotesFromStops,
} from '../src/shared/stopTimeFootnotes';
import type { WizardCfg } from '../src/wizard/utils/wizardCfgTypes';

describe('stopTimeFootnotes', () => {
  const cfg: WizardCfg = {
    onRequestPickupFootnote: 'Ge tecken till föraren vid påstigning.',
    onRequestDropoffFootnote: 'Säg till konduktören vid avstigning.',
  };

  it('footnoteMarksForStop returns P and A when both restrictions apply', () => {
    expect(
      footnoteMarksForStop({ on_request_pickup: true, on_request_dropoff: true }),
    ).toEqual(['P', 'A']);
  });

  it('footnoteMarksForStop returns P only for pickup', () => {
    expect(footnoteMarksForStop({ on_request_pickup: true })).toEqual(['P']);
  });

  it('tripFootnotesFromStops deduplicates marks across stops', () => {
    const entries = tripFootnotesFromStops(
      [
        { on_request_pickup: true },
        { on_request_dropoff: true },
        { on_request_pickup: true, on_request_dropoff: true },
      ],
      cfg,
    );
    expect(entries).toEqual([
      { mark: 'P', text: cfg.onRequestPickupFootnote },
      { mark: 'A', text: cfg.onRequestDropoffFootnote },
    ]);
  });

  it('stopTimeFootnotesForSegment returns footnote texts only', () => {
    const notes = stopTimeFootnotesForSegment([{ on_request_pickup: true }], cfg);
    expect(notes).toEqual(['Ge tecken till föraren vid påstigning.']);
  });
});
