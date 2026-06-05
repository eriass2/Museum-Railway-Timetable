import { describe, expect, it } from 'vitest';
import {
  arrivalAtDestination,
  connectionDoorToDoorMinutes,
  connectionLegs,
  departureFromOrigin,
  isTransfer,
} from '../src/wizard/utils/connection';
import type { JourneyConnection } from '../src/wizard/types';

const direct: JourneyConnection = {
  service_id: 1,
  from_departure: '10:00',
  to_arrival: '11:30',
};

describe('connection utils', () => {
  it('reads departure and arrival from connection', () => {
    expect(departureFromOrigin(direct)).toBe('10:00');
    expect(arrivalAtDestination(direct)).toBe('11:30');
  });

  it('synthesizes single leg when legs missing', () => {
    const legs = connectionLegs(direct);
    expect(legs).toHaveLength(1);
    expect(legs[0].service_id).toBe(1);
  });

  it('passes service_number and destination on synthetic leg', () => {
    const conn: JourneyConnection = {
      service_id: 101,
      service_number: '101',
      train_type: 'Rälsbuss',
      train_type_slug: 'ralsbuss',
      destination: 'Faringe',
      from_departure: '16:45',
      to_arrival: '17:45',
    };
    const leg = connectionLegs(conn)[0];
    expect(leg.service_number).toBe('101');
    expect(leg.destination).toBe('Faringe');
    expect(leg.train_type).toBe('Rälsbuss');
    expect(leg.train_type_slug).toBe('ralsbuss');
  });

  it('detects transfer connections', () => {
    expect(isTransfer(direct)).toBe(false);
    expect(isTransfer({ ...direct, legs: [{ service_id: 1 }, { service_id: 2 }] })).toBe(true);
    expect(isTransfer({ ...direct, connection_type: 'transfer' })).toBe(true);
  });

  it('door-to-door minutes include transfer wait', () => {
    const transfer: JourneyConnection = {
      service_id: 1,
      connection_type: 'transfer',
      from_departure: '11:10',
      to_arrival: '13:47',
      duration_minutes: 44,
      legs: [
        { service_id: 1, from_departure: '11:10', to_arrival: '12:00' },
        { service_id: 2, from_departure: '12:20', to_arrival: '13:47' },
      ],
    };
    expect(connectionDoorToDoorMinutes(transfer)).toBe(157);
    expect(connectionDoorToDoorMinutes(direct)).toBe(90);
  });
});
