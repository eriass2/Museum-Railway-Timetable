import { ref } from 'vue';
import { getStopTimes, saveStopTimes } from '../api/adminRest';
import type { StopTimeRow } from '../types';
import type { TimetableTimeCellEdit } from '../../types/timetableOverview';

function hhmmToInput(value: string): string {
  if (!value || !/^\d{1,2}:\d{2}$/.test(value)) {
    return '';
  }
  const [h, m] = value.split(':');
  return `${h.padStart(2, '0')}:${m}`;
}

function inputToHhmm(value: string): string {
  if (!value) {
    return '';
  }
  const [h, m] = value.split(':');
  return `${h.padStart(2, '0')}:${m}`;
}

export function useOverviewGridEdit() {
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
      const stops = rows.map((s) => ({
        station_id: s.id,
        stops_here: s.stops_here ? '1' : '0',
        arrival: s.arrival_time || '',
        departure: s.departure_time || '',
        pickup: s.pickup_allowed ? '1' : '',
        dropoff: s.dropoff_allowed ? '1' : '',
      }));
      const res = await saveStopTimes(serviceId, stops);
      cache.value.set(serviceId, res.stations);
      message.value = 'Stopptid sparad';
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Kunde inte spara';
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

  return {
    saving,
    error,
    message,
    hhmmToInput,
    inputToHhmm,
    applyCellEdit,
    mergeEdit,
  };
}

export type OverviewGridEdit = ReturnType<typeof useOverviewGridEdit>;
