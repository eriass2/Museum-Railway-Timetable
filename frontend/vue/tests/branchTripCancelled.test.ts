import { describe, expect, it } from 'vitest';
import { branchTripIsCancelled, branchTripNoticeDetail } from '../src/shared/branchTripCancelled';

describe('branchTripIsCancelled', () => {
  it('uses trip flag or notice text', () => {
    expect(branchTripIsCancelled({ trip: '1', fromTime: '10:00', toTime: '10:30', isCancelled: true, connectingTrains: [] })).toBe(true);
    expect(
      branchTripIsCancelled({
        trip: '1',
        fromTime: '10:00',
        toTime: '10:30',
        deviationNotice: 'Inställd',
        connectingTrains: [],
      }),
    ).toBe(true);
  });
});

describe('branchTripNoticeDetail', () => {
  it('hides default cancelled label only', () => {
    expect(
      branchTripNoticeDetail(
        { trip: '1', fromTime: '10:00', toTime: '10:30', deviationNotice: 'Inställd', isCancelled: true, connectingTrains: [] },
        'Inställd',
      ),
    ).toBe('');
    expect(
      branchTripNoticeDetail(
        { trip: '1', fromTime: '10:00', toTime: '10:30', deviationNotice: 'Inställd pga väder', isCancelled: true, connectingTrains: [] },
        'Inställd',
      ),
    ).toBe('Inställd pga väder');
  });
});
