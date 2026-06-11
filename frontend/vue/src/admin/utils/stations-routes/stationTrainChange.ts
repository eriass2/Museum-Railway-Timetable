export type TrainChangeMap = Record<string, { typeName: string; serviceNumber: string }>;

export type TrainChangeEntry = {
  from_service: string;
  type_name: string;
  to_service: string;
};

export type TrainChangeValidation = {
  incompleteRows: number[];
  duplicateFromServices: string[];
  duplicateToServices: string[];
};

export function emptyTrainChangeEntry(): TrainChangeEntry {
  return { from_service: '', type_name: '', to_service: '' };
}

export function trainChangeMapToEntries(map: TrainChangeMap | undefined): TrainChangeEntry[] {
  if (!map) {
    return [];
  }
  return Object.entries(map).map(([from_service, transfer]) => ({
    from_service,
    type_name: transfer.typeName,
    to_service: transfer.serviceNumber,
  }));
}

export function trainChangeEntriesToMap(entries: TrainChangeEntry[]): TrainChangeMap {
  const map: TrainChangeMap = {};
  for (const row of entries) {
    const from = row.from_service.trim();
    const type = row.type_name.trim();
    const to = row.to_service.trim();
    if (!from || !type || !to) {
      continue;
    }
    map[from] = { typeName: type, serviceNumber: to };
  }
  return map;
}

function duplicateValues(values: string[]): string[] {
  const seen = new Set<string>();
  const dupes = new Set<string>();
  for (const value of values) {
    if (seen.has(value)) {
      dupes.add(value);
    }
    seen.add(value);
  }
  return [...dupes];
}

export function validateTrainChangeEntries(entries: TrainChangeEntry[]): TrainChangeValidation {
  const incompleteRows: number[] = [];
  const completeRows: TrainChangeEntry[] = [];
  entries.forEach((row, index) => {
    const values = [row.from_service, row.type_name, row.to_service].map((value) => value.trim());
    if (values.every((value) => value === '')) {
      return;
    }
    if (values.some((value) => value === '')) {
      incompleteRows.push(index + 1);
      return;
    }
    completeRows.push(row);
  });
  return {
    incompleteRows,
    duplicateFromServices: duplicateValues(completeRows.map((row) => row.from_service.trim())),
    duplicateToServices: duplicateValues(completeRows.map((row) => row.to_service.trim())),
  };
}

export function trainChangeEntryCount(map: TrainChangeMap | undefined): number {
  return trainChangeMapToEntries(map).length;
}

export function syncStationTrainChangeEntries(
  station: { train_change_map?: TrainChangeMap },
  entries: TrainChangeEntry[],
): void {
  station.train_change_map = trainChangeEntriesToMap(entries);
}

export function appendTrainChangeEntry(station: { train_change_map?: TrainChangeMap }): TrainChangeEntry[] {
  const entries = trainChangeMapToEntries(station.train_change_map);
  entries.push(emptyTrainChangeEntry());
  syncStationTrainChangeEntries(station, entries);
  return entries;
}

export function removeTrainChangeEntry(
  station: { train_change_map?: TrainChangeMap },
  index: number,
): TrainChangeEntry[] {
  const entries = trainChangeMapToEntries(station.train_change_map);
  entries.splice(index, 1);
  syncStationTrainChangeEntries(station, entries);
  return entries;
}
