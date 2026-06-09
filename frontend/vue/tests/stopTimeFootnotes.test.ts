import { describe, expect, it } from 'vitest';
import { stopTimeFootnotesForSegment } from '../src/shared/stopTimeFootnotes';
import type { WizardCfg } from '../src/wizard/utils/wizardCfgTypes';

describe('stopTimeFootnotesForSegment', () => {
  const cfg: WizardCfg = {
    onRequestPickupFootnote: 'Ge tecken till föraren vid påstigning.',
    onRequestDropoffFootnote: 'Säg till konduktören vid avstigning.',
  };

  it('returns pickup footnote when on_request_pickup is set', () => {
    const notes = stopTimeFootnotesForSegment([{ on_request_pickup: true }], cfg);
    expect(notes).toEqual(['Ge tecken till föraren vid påstigning.']);
  });

  it('returns both footnotes for on_request_both', () => {
    const notes = stopTimeFootnotesForSegment([{ on_request_both: true }], cfg);
    expect(notes).toHaveLength(2);
  });
});
