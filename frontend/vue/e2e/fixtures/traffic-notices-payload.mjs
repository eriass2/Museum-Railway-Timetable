/** Sample public traffic notices payload for static E2E. */
export function buildSampleTrafficNoticesPayload() {
  return {
    reference_date: '2026-06-06',
    days: 1,
    general: [{ id: 'e2e-1', text: 'Glassrean i caféet kl 14–16!' }],
    by_date: [
      {
        date: '2026-06-06',
        date_label: 'Idag fredag 6 juni',
        deviations: [
          {
            service_id: 71,
            service_number: '71',
            route_label: 'Uppsala Östra – Faringe',
            trip_label: 'Uppsala Östra → Marielund',
            notice: 'Inställd',
            is_cancelled: true,
            train_type_id: 1,
          },
        ],
      },
    ],
    is_empty: false,
  };
}

export function buildEmptyTrafficNoticesPayload() {
  return {
    reference_date: '2026-06-06',
    days: 1,
    general: [],
    by_date: [],
    is_empty: true,
  };
}
