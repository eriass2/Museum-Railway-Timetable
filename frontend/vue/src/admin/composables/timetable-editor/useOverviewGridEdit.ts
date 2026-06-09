import { ref } from 'vue';
import { getStopTimes, saveStopTimes } from '../../api/adminRest';
import type { StopTimeRow } from '../../types';
import type { TimetableTimeCellEdit } from '../../../types/timetableOverview';
import { adminConfig } from '../../types';
import { adminErrorMessage, adminStr } from '../../utils/adminLabels';
import { stopTimesToApiPayload } from '../../utils/timetable-editor/stopTimesPayload';
import { padHhmm } from '../../../utils/datetime';

export function useOverviewGridEdit() {
  const cfg = adminConfig();
  const cache = ref(new Map<number, StopTimeRow[]>());
  const saving = ref(new Set<number>());
  const error = ref('');
  const message = ref('');

  async function ensureService(serviceId: number): Promise<StopTimeRow[]> {
    const hit = cache.value.get(serviceId);
    if (hit) {
      return hit;
    }
    const res = await getStopTimes(serviceId);
    cache.value.set(serviceId, res.stations.map((s) => ({ ...s })));
    return cache.value.get(serviceId)!;
  }

  async function applyCellEdit(
    serviceId: number,
    stationId: number,
    edit: TimetableTimeCellEdit,
  ): Promise<void> {
    error.value = '';
    const rows = await ensureService(serviceId);
    let row = rows.find((s) => s.id === stationId);
    if (!row && edit.stopsHere) {
      row = {
        id: stationId,
        name: '',
        sequence: rows.length + 1,
        stops_here: true,
        arrival_time: edit.arrival,
        departure_time: edit.departure,
        pickup_allowed: edit.pickupAllowed,
        dropoff_allowed: edit.dropoffAllowed,
      };
      rows.push(row);
    }
    if (!row) {
      return;
    }
    row.stops_here = edit.stopsHere;
    row.arrival_time = edit.arrival;
    row.departure_time = edit.departure;
    row.pickup_allowed = edit.pickupAllowed;
    row.dropoff_allowed = edit.dropoffAllowed;
    cache.value.set(serviceId, [...rows]);
    await persistService(serviceId);
  }

  async function persistService(serviceId: number): Promise<void> {
    const rows = cache.value.get(serviceId);
    if (!rows) {
      return;
    }
    saving.value.add(serviceId);
    try {
      const res = await saveStopTimes(serviceId, stopTimesToApiPayload(rows), true);
      cache.value.set(serviceId, res.stations);
      message.value = adminStr(cfg, 'stopTimesSaved');
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'saveFailed');
    } finally {
      saving.value.delete(serviceId);
    }
  }

  function mergeEdit(
    serviceId: number,
    stationId: number,
    cell: TimetableTimeCellEdit | undefined,
    patch: Partial<TimetableTimeCellEdit>,
  ): TimetableTimeCellEdit {
    const base = cell ?? {
      arrival: '',
      departure: '',
      stopsHere: false,
      pickupAllowed: true,
      dropoffAllowed: true,
    };
    void serviceId;
    void stationId;
    return { ...base, ...patch };
  }

  function clearCache(): void {
    cache.value = new Map();
  }

  return {
    saving,
    error,
    message,
    hhmmToInput: padHhmm,
    inputToHhmm: padHhmm,
    applyCellEdit,
    mergeEdit,
    clearCache,
  };
}

export type OverviewGridEdit = ReturnType<typeof useOverviewGridEdit>;
