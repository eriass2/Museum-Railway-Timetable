import { describe, expect, it } from 'vitest';
import {
  createDeviationRow,
  deviationsToSavePayload,
  formatDeviationTripLabel,
  hasDeviationRow,
  toggleCancelledDeviationNotice,
  type DeviationRow,
} from '../src/admin/utils/timetable-editor/deviationsPayload';

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

describe('deviation row helpers', () => {
  const service = {
    id: 10,
    service_number: '71',
    destination: 'Faringe',
    route_name: 'Uppsala – Faringe',
    train_type_id: 3,
  };

  it('formats trip label with service number', () => {
    expect(formatDeviationTripLabel(service)).toBe('71 — Faringe');
  });

  it('creates a new deviation row', () => {
    expect(createDeviationRow(service, '2026-06-01')).toEqual({
      service_id: 10,
      date: '2026-06-01',
      trip_label: '71 — Faringe',
      train_type_id: 3,
      notice: '',
    });
  });

  it('detects duplicate service/date pairs', () => {
    const rows: DeviationRow[] = [
      {
        service_id: 10,
        date: '2026-06-01',
        trip_label: '71 — Faringe',
        train_type_id: 0,
        notice: '',
      },
    ];
    expect(hasDeviationRow(rows, 10, '2026-06-01')).toBe(true);
    expect(hasDeviationRow(rows, 10, '2026-06-02')).toBe(false);
  });

  it('toggles cancelled notice text', () => {
    expect(toggleCancelledDeviationNotice('', true)).toBe('Inställd');
    expect(toggleCancelledDeviationNotice('Inställd', false)).toBe('');
    expect(toggleCancelledDeviationNotice('Försening', false)).toBe('Försening');
  });
});
