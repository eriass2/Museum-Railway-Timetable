/** Sample disruption feed payload for static E2E (UL-style panels). */
export function buildSampleDisruptionFeedPayload() {
  const ongoingGeneral = {
    id: 'notice-e2e-1',
    source: 'general',
    kind: 'info',
    phase: 'ongoing',
    date_from: '2026-06-06',
    date_to: '2026-06-06',
    date_label: 'Idag',
    headline: 'Glassrea på Faringe station kl 14.',
    summary: 'Glassrea på Faringe station kl 14.',
    validity_label: 'Gäller Idag',
    line_label: '',
    severity: 'info',
    category_key: 'general',
    category_label: 'Information',
    icon_key: 'diesel',
    body: 'Glassrea på Faringe station kl 14.\nGlassrea på stationen idag.',
    route_label: '',
    detail_intro: 'Glassrea på stationen idag.',
    detail_sections: [],
    train_numbers: [],
    service_ids: [],
  };

  const ongoingBusDeviation = {
    id: 'deviation-e2e-bus',
    source: 'deviation',
    kind: 'deviation',
    phase: 'ongoing',
    date_from: '2026-06-06',
    date_to: '2026-06-06',
    date_label: 'Idag',
    headline: 'Försenad trafik',
    summary: 'Försenad trafik',
    validity_label: 'Gäller Idag',
    line_label: 'B3',
    severity: 'warning',
    category_key: 'bus',
    category_label: 'Buss',
    icon_key: 'bus',
    body: 'Försenad trafik',
    route_label: 'Selknä – Fjällnora',
    detail_intro: '',
    detail_sections: [],
    train_numbers: ['B3'],
    service_ids: [303],
  };

  const ongoingDeviation = {
    id: 'deviation-e2e-1',
    source: 'deviation',
    kind: 'cancelled',
    phase: 'ongoing',
    date_from: '2026-06-06',
    date_to: '2026-06-06',
    date_label: 'Idag',
    headline: 'Inställd trafik',
    summary: 'Inställd trafik',
    validity_label: 'Gäller Idag',
    line_label: '71',
    severity: 'warning',
    category_key: 'train',
    category_label: 'Tåg',
    icon_key: 'diesel',
    body: 'Inställd',
    route_label: 'Faringe – Uppsala',
    detail_intro: 'Tågen trafikerar inte enligt ordinarie tidtabell denna dag.',
    detail_sections: [
      {
        title: 'Faringe – Uppsala',
        lines: ['71 → Uppsala Östra'],
      },
    ],
    train_numbers: ['71'],
    service_ids: [71],
  };

  const upcomingGeneral = {
    id: 'notice-e2e-2',
    source: 'general',
    kind: 'info',
    phase: 'upcoming',
    date_from: '2026-07-01',
    date_to: '2026-08-16',
    date_label: '1 juli – 16 augusti',
    headline: 'Buss ersätter tåg vid Selkné.',
    summary: 'Buss ersätter tåg vid Selkné.',
    validity_label: 'Gäller 1 juli – 16 augusti',
    line_label: '',
    severity: 'info',
    category_key: 'general',
    category_label: 'Information',
    icon_key: 'diesel',
    body: 'Buss ersätter tåg vid Selkné.\nBerörda anslutningar: B3, B4.',
    route_label: '',
    detail_intro: 'Berörda anslutningar: B3, B4.',
    detail_sections: [],
    train_numbers: [],
    service_ids: [],
  };

  const ongoing = [ongoingGeneral, ongoingDeviation, ongoingBusDeviation];
  const upcoming = [upcomingGeneral];

  return {
    reference_date: '2026-06-06',
    horizon_days: 90,
    end_date: '2026-09-04',
    ongoing,
    upcoming,
    items: [...ongoing, ...upcoming],
    panels: [
      {
        key: 'ongoing',
        title: 'Aktuellt trafikläge',
        icon: 'clock',
        categories: [
          {
            key: 'train',
            label: 'Tåg',
            icon_key: 'diesel',
            counts: { info: 0, warning: 1 },
            items: [ongoingDeviation],
          },
          {
            key: 'bus',
            label: 'Buss',
            icon_key: 'bus',
            counts: { info: 0, warning: 1 },
            items: [ongoingBusDeviation],
          },
          {
            key: 'general',
            label: 'Information',
            icon_key: 'diesel',
            counts: { info: 1, warning: 0 },
            items: [ongoingGeneral],
          },
        ],
      },
      {
        key: 'upcoming',
        title: 'Planerade avvikelser',
        icon: 'calendar',
        categories: [
          {
            key: 'general',
            label: 'Information',
            icon_key: 'diesel',
            counts: { info: 1, warning: 0 },
            items: [upcomingGeneral],
          },
        ],
      },
    ],
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
    panels: [],
    is_empty: true,
  };
}

/** Ongoing empty, upcoming only — TF-C8 edge case. */
export function buildUpcomingOnlyDisruptionFeedPayload() {
  const sample = buildSampleDisruptionFeedPayload();
  const upcoming = sample.upcoming;
  const upcomingPanel = sample.panels.find((panel) => panel.key === 'upcoming');
  return {
    reference_date: sample.reference_date,
    horizon_days: sample.horizon_days,
    end_date: sample.end_date,
    ongoing: [],
    upcoming,
    items: upcoming,
    panels: upcomingPanel ? [upcomingPanel] : [],
    is_empty: false,
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
