import { afterEach, describe, expect, it, vi } from 'vitest';
import type { WizardVueConfig } from '../src/config/types';
import * as connectionDetailLoad from '../src/wizard/composables/connectionDetailLoad';
import {
  enrichTripSummaryInputWithFootnotes,
  loadTripSummaryLegFootnotes,
} from '../src/wizard/utils/tripSummaryFootnotes';
import type { WizardStore } from '../src/wizard/store/wizardStoreTypes';

const config = { restUrl: '/wp-json/test/v1/', app: 'wizard' } as WizardVueConfig;
const cfg = {
  onRequestPickupFootnote: 'Ge tecken till föraren om du vill stiga på.',
  onRequestDropoffFootnote: 'Säg till konduktören i god tid om du vill stiga av.',
};

const connection = {
  service_id: 93,
  from_departure: '10:00',
  to_arrival: '11:43',
  legs: [],
} as WizardStore['outbound'];

describe('loadTripSummaryLegFootnotes', () => {
  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('returns deduplicated footnote texts from connection detail stops', async () => {
    vi.spyOn(connectionDetailLoad, 'loadConnectionDetailSegments').mockResolvedValue([
      {
        title: '',
        notice: '',
        stops: [{ behov_hint: 'dropoff' }, { behov_hint: 'dropoff' }],
      },
    ]);

    const footnotes = await loadTripSummaryLegFootnotes({
      config,
      cfg,
      connection: connection!,
      legCtx: 'outbound',
      fromId: 1,
      toId: 2,
      dateYmd: '2026-07-15',
    });

    expect(footnotes).toEqual(['Säg till konduktören i god tid om du vill stiga av.']);
  });

  it('returns empty list when detail load fails', async () => {
    vi.spyOn(connectionDetailLoad, 'loadConnectionDetailSegments').mockResolvedValue([]);

    const footnotes = await loadTripSummaryLegFootnotes({
      config,
      cfg,
      connection: connection!,
      legCtx: 'outbound',
      fromId: 1,
      toId: 2,
      dateYmd: '2026-07-15',
    });

    expect(footnotes).toEqual([]);
  });
});

describe('enrichTripSummaryInputWithFootnotes', () => {
  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('adds footnotes to matching legs', async () => {
    vi.spyOn(connectionDetailLoad, 'loadConnectionDetailSegments').mockResolvedValue([
      {
        title: '',
        notice: '',
        stops: [{ behov_hint: 'dropoff' }],
      },
    ]);

    const store = {
      fromId: 1,
      toId: 2,
      dateYmd: '2026-07-15',
      tripType: 'single',
      outbound: connection,
      inbound: null,
    } as WizardStore;

    const enriched = await enrichTripSummaryInputWithFootnotes(
      {
        title: 'Din resa',
        downloadName: 'A → B',
        tripTypeLabel: 'Enkel resa',
        legs: [
          {
            heading: 'Utresa',
            route: 'A → B',
            timeRange: '10.00 – 11.43',
            date: '15 juli 2026',
          },
        ],
      },
      store,
      config,
      cfg,
    );

    expect(enriched.legs[0]?.footnotes).toEqual([
      'Säg till konduktören i god tid om du vill stiga av.',
    ]);
  });
});
