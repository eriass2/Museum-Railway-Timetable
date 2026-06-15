import { describe, expect, it } from 'vitest';
import {
  stopShowsOnRequestInfo,
  tripFootnotesFromStops,
} from '../src/shared/stopTimeFootnotes';

const cfg = {
  onRequestPickupFootnote: 'Ge tecken till föraren om du vill stiga på.',
  onRequestDropoffFootnote: 'Säg till konduktören i god tid om du vill stiga av.',
};

describe('stopShowsOnRequestInfo', () => {
  it('shows icon for pickup or dropoff hints', () => {
    expect(stopShowsOnRequestInfo({ behov_hint: 'pickup' })).toBe(true);
    expect(stopShowsOnRequestInfo({ behov_hint: 'dropoff' })).toBe(true);
    expect(stopShowsOnRequestInfo({ behov_hint: 'both' })).toBe(true);
    expect(stopShowsOnRequestInfo({ behov_hint: '' })).toBe(false);
  });
});

describe('tripFootnotesFromStops', () => {
  it('deduplicates footnotes by text', () => {
    const entries = tripFootnotesFromStops(
      [
        { behov_hint: 'dropoff' },
        { behov_hint: 'dropoff' },
      ],
      cfg,
    );
    expect(entries).toHaveLength(1);
    expect(entries[0]?.text).toContain('stiga av');
  });

  it('uses dropoff copy for both hint', () => {
    const entries = tripFootnotesFromStops([{ behov_hint: 'both' }], cfg);
    expect(entries).toHaveLength(1);
    expect(entries[0]?.text).toContain('stiga av');
  });

  it('uses pickup copy for pickup hint', () => {
    const entries = tripFootnotesFromStops([{ behov_hint: 'pickup' }], cfg);
    expect(entries[0]?.text).toContain('stiga på');
  });
});
