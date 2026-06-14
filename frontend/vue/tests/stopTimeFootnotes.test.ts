import { describe, expect, it } from 'vitest';
import {
  ON_REQUEST_INFO_MARK,
  stopShowsOnRequestInfo,
  tripFootnotesFromStops,
} from '../src/shared/stopTimeFootnotes';
import type { WizardCfg } from '../src/wizard/utils/wizardCfgTypes';

describe('stopTimeFootnotes', () => {
  const cfg: WizardCfg = {
    onRequestPickupFootnote: 'Behovsuppehåll, ge ett tecken till föraren om du vill stiga på.',
    onRequestDropoffFootnote:
      'Behovsuppehåll, säg till konduktören i god tid om du vill stiga av.',
  };

  it('stopShowsOnRequestInfo is true when dropoff restriction applies', () => {
    expect(stopShowsOnRequestInfo({ on_request_dropoff: true })).toBe(true);
  });

  it('stopShowsOnRequestInfo is false for passed-through stops without flags', () => {
    expect(stopShowsOnRequestInfo({})).toBe(false);
  });

  it('tripFootnotesFromStops deduplicates texts and uses info mark', () => {
    const entries = tripFootnotesFromStops(
      [
        { on_request_dropoff: true },
        { on_request_dropoff: true },
      ],
      cfg,
    );
    expect(entries).toEqual([
      { mark: ON_REQUEST_INFO_MARK, text: cfg.onRequestDropoffFootnote },
    ]);
  });

  it('tripFootnotesFromStops shows dropoff footnote for alighting stop', () => {
    const entries = tripFootnotesFromStops([{ on_request_dropoff: true }], cfg);
    expect(entries).toEqual([
      { mark: ON_REQUEST_INFO_MARK, text: cfg.onRequestDropoffFootnote },
    ]);
  });
});
