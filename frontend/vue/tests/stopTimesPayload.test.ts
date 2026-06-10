import { describe, expect, it } from 'vitest';
import { stopTimesToApiPayload } from '../src/admin/utils/timetable-editor/stopTimesPayload';

describe('stopTimesToApiPayload', () => {
  it('maps editor rows to REST stops', () => {
    expect(
      stopTimesToApiPayload([
        {
          id: 5,
          name: 'Uppsala',
          sequence: 1,
          stops_here: true,
          arrival_time: '10:00',
          departure_time: '10:05',
          pickup_allowed: true,
          dropoff_allowed: false,
          approximate_time: true,
        },
      ]),
    ).toEqual([
      {
        station_id: 5,
        stops_here: '1',
        arrival: '10:00',
        departure: '10:05',
        pickup: '1',
        dropoff: '',
        approximate: '1',
      },
    ]);
  });
});
