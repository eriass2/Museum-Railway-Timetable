import type { TimetableTimeCellEdit } from '../../../types/timetableOverview';

export function gridRowShowsArrival(rowKind: string): boolean {
  return rowKind === 'to' || rowKind === 'arrival' || rowKind === 'station';
}

export function gridRowShowsDeparture(rowKind: string): boolean {
  return rowKind === 'from' || rowKind === 'departure' || rowKind === 'station';
}

/** A5: no time on row => train does not stop; unchecked stop clears times. */
export function finalizeGridCellEdit(
  edit: TimetableTimeCellEdit,
  rowKind: string,
): TimetableTimeCellEdit {
  const arrival = gridRowShowsArrival(rowKind) ? edit.arrival : '';
  const departure = gridRowShowsDeparture(rowKind) ? edit.departure : '';
  const hasTime = arrival !== '' || departure !== '';
  if (!edit.stopsHere || !hasTime) {
    return {
      ...edit,
      arrival: '',
      departure: '',
      stopsHere: false,
    };
  }
  return {
    ...edit,
    arrival,
    departure,
    stopsHere: true,
  };
}
