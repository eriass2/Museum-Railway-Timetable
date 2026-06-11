/** Sample disruption feed payload for static E2E. */
export function buildSampleDisruptionFeedPayload() {
  return {
    reference_date: '2026-06-06',
    horizon_days: 90,
    end_date: '2026-09-04',
    ongoing: [
      {
        id: 'notice-e2e-1',
        source: 'general',
        kind: 'info',
        phase: 'ongoing',
        date_from: '2026-06-06',
        date_to: '2026-06-06',
        date_label: 'Idag',
        headline: 'Glassrean i caféet kl 14–16!',
        body: 'Glassrean i caféet kl 14–16!',
        train_numbers: [],
        service_ids: [],
      },
      {
        id: 'deviation-e2e-1',
        source: 'deviation',
        kind: 'cancelled',
        phase: 'ongoing',
        date_from: '2026-06-06',
        date_to: '2026-06-06',
        date_label: 'Idag',
        headline: 'Inställd trafik — Tåg 71',
        body: 'Inställd',
        train_numbers: ['71'],
        service_ids: [71],
      },
    ],
    upcoming: [],
    items: [],
    is_empty: false,
  };
}

export function buildEmptyDisruptionFeedPayload() {
  return {
    reference_date: '2026-06-06',
    horizon_days: 90,
    end_date: '2026-09-04',
    ongoing: [],
    upcoming: [],
    items: [],
    is_empty: true,
  };
}

/** @deprecated Use buildSampleDisruptionFeedPayload — kept for legacy REST stubs. */
export function buildSampleTrafficNoticesPayload() {
  return buildSampleDisruptionFeedPayload();
}

/** @deprecated Use buildEmptyDisruptionFeedPayload. */
export function buildEmptyTrafficNoticesPayload() {
  return buildEmptyDisruptionFeedPayload();
}
