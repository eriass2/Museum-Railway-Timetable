import { computed, ref, watch, type Ref } from 'vue';
import { adminStr } from '../../utils/adminLabels';
import type { AdminClientConfig } from '../../types';
import type { StationRow } from '../../types';
import {
  emptyTrainChangeEntry,
  syncStationTrainChangeEntries,
  trainChangeMapToEntries,
  validateTrainChangeEntries,
  type TrainChangeEntry,
} from '../../utils/stations-routes/stationTrainChange';

export function useStationTrainChangeDraft(
  station: Ref<StationRow>,
  cfg: AdminClientConfig,
) {
  const draftEntries = ref<TrainChangeEntry[]>([emptyTrainChangeEntry()]);

  watch(
    () => station.value.id,
    () => {
      const rows = trainChangeMapToEntries(station.value.train_change_map);
      draftEntries.value = rows.length ? rows : [emptyTrainChangeEntry()];
    },
    { immediate: true },
  );

  const validation = computed(() => validateTrainChangeEntries(draftEntries.value));

  const warnings = computed(() => {
    const messages: string[] = [];
    if (validation.value.incompleteRows.length) {
      messages.push(
        adminStr(cfg, 'stationsTrainChangeIncomplete', 'Rader med saknade fält sparas inte.'),
      );
    }
    if (validation.value.duplicateFromServices.length) {
      messages.push(
        adminStr(
          cfg,
          'stationsTrainChangeDuplicateFrom',
          'Samma ankommande tågnummer används flera gånger.',
        ),
      );
    }
    if (validation.value.duplicateToServices.length) {
      messages.push(
        adminStr(
          cfg,
          'stationsTrainChangeDuplicateTo',
          'Samma continuation-tur används flera gånger.',
        ),
      );
    }
    return messages;
  });

  function updateEntry(index: number, patch: Partial<TrainChangeEntry>): void {
    const next = [...draftEntries.value];
    next[index] = { ...next[index], ...patch };
    draftEntries.value = next;
    syncStationTrainChangeEntries(station.value, next);
  }

  function addRow(): void {
    draftEntries.value = [...draftEntries.value, emptyTrainChangeEntry()];
    syncStationTrainChangeEntries(station.value, draftEntries.value);
  }

  function removeRow(index: number): void {
    const next = [...draftEntries.value];
    next.splice(index, 1);
    draftEntries.value = next;
    if (!draftEntries.value.length) {
      draftEntries.value = [emptyTrainChangeEntry()];
    }
    syncStationTrainChangeEntries(station.value, draftEntries.value);
  }

  return { draftEntries, warnings, updateEntry, addRow, removeRow };
}
