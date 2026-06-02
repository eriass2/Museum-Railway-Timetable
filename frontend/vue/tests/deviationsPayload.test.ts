import { describe, expect, it } from 'vitest';
import { deviationsToSavePayload } from '../src/admin/utils/deviationsPayload';

describe('deviationsToSavePayload', () => {
  it('groups rows by service and date', () => {
    const payload = deviationsToSavePayload([
      {
        service_id: 10,
        date: '2026-06-01',
        trip_label: 'Tåg 71',
        train_type_id: 3,
        notice: 'Försening',
      },
      {
        service_id: 10,
        date: '2026-06-02',
        trip_label: 'Tåg 71',
        train_type_id: 0,
        notice: '',
      },
    ]);

    expect(payload).toEqual({
      10: {
        '2026-06-01': { train_type: 3, notice: 'Försening' },
        '2026-06-02': { notice: undefined },
      },
    });
  });
});
