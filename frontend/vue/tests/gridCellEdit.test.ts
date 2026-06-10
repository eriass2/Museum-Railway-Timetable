import { describe, expect, it } from 'vitest';
import { finalizeGridCellEdit } from '../src/admin/utils/timetable-editor/gridCellEdit';
import type { TimetableTimeCellEdit } from '../src/types/timetableOverview';

function edit(overrides: Partial<TimetableTimeCellEdit> = {}): TimetableTimeCellEdit {
  return {
    arrival: '',
    departure: '',
    stopsHere: false,
    pickupMode: 'scheduled',
    dropoffMode: 'scheduled',
    approximateTime: false,
    ...overrides,
  };
}

describe('finalizeGridCellEdit', () => {
  it('clears stop when no times are set (A5)', () => {
    const result = finalizeGridCellEdit(
      edit({ stopsHere: true, arrival: '', departure: '' }),
      'station',
    );
    expect(result.stopsHere).toBe(false);
    expect(result.arrival).toBe('');
    expect(result.departure).toBe('');
  });

  it('keeps stop when a time is set', () => {
    const result = finalizeGridCellEdit(
      edit({ stopsHere: true, departure: '09:00' }),
      'from',
    );
    expect(result.stopsHere).toBe(true);
    expect(result.departure).toBe('09:00');
  });

  it('clears times when stop is unchecked', () => {
    const result = finalizeGridCellEdit(
      edit({ stopsHere: false, arrival: '10:00', departure: '10:05' }),
      'station',
    );
    expect(result.stopsHere).toBe(false);
    expect(result.arrival).toBe('');
    expect(result.departure).toBe('');
  });
});
