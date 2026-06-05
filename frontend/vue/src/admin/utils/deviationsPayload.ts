export type DeviationRow = {
  service_id: number;
  date: string;
  trip_label: string;
  train_type_id: number;
  notice: string;
};

export type DeviationSavePayload = Record<
  number,
  Record<string, { train_type?: number; notice?: string }>
>;

type DeviationTripSource = {
  id: number;
  service_number: string;
  destination: string;
  route_name: string;
  train_type_id: number;
};

/** Label for deviation tables (tur + destination). */
export function formatDeviationTripLabel(service: DeviationTripSource): string {
  const dest = service.destination || service.route_name;
  return `${service.service_number} — ${dest}`;
}

export function hasDeviationRow(
  rows: DeviationRow[],
  serviceId: number,
  date: string,
): boolean {
  return rows.some((row) => row.service_id === serviceId && row.date === date);
}

export function createDeviationRow(
  service: DeviationTripSource,
  date: string,
): DeviationRow {
  return {
    service_id: service.id,
    date,
    trip_label: formatDeviationTripLabel(service),
    train_type_id: service.train_type_id || 0,
    notice: '',
  };
}

/** Aggregate deviation rows for `saveDeviations` REST body. */
export function deviationsToSavePayload(rows: DeviationRow[]): DeviationSavePayload {
  const byService: DeviationSavePayload = {};
  for (const row of rows) {
    if (!byService[row.service_id]) {
      byService[row.service_id] = {};
    }
    byService[row.service_id][row.date] = {
      train_type: row.train_type_id || undefined,
      notice: row.notice || undefined,
    };
  }
  return byService;
}
