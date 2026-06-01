import { describe, expect, it } from 'vitest';
import {
  arrivalAtDestination,
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
});
