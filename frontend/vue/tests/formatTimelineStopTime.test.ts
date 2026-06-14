import { describe, expect, it } from 'vitest';
import { formatTimelineStopTime } from '../src/shared/formatTimelineStopTime';

describe('formatTimelineStopTime', () => {
  it('prefers pre-formatted time_label from API', () => {
    expect(
      formatTimelineStopTime({
        station_title: 'Lövstahagen',
        time_label: 'Ca 10.46',
        departure_time: '10:46',
      }),
    ).toBe('Ca 10.46');
  });

  it('formats raw clock fields when time_label is absent', () => {
    expect(
      formatTimelineStopTime({
        station_title: 'Uppsala',
        departure_time: '10:00',
      }),
    ).toBe('10.00');
  });
});
