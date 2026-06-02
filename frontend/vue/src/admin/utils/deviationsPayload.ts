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
