import { describe, expect, it } from 'vitest';
import {
  applyLineStationMove,
  applyRouteDraftToLine,
  lineRowToRouteDraft,
  removeLineStation,
} from '../src/admin/utils/stations-routes/lineRouteEditor';
import type { LineRow } from '../src/admin/types';

const sampleLine: LineRow = {
  code: 'fjallnora',
  title: 'Selkné – Fjällnora',
  kind: 'branch',
  station_ids: [10, 20],
  start_station: 10,
  end_station: 20,
  junction_station_id: 10,
  junction_station_code: 'selkna',
  junction_station_name: 'Selkné',
  requires_transfer: true,
  bidirectional: true,
};

describe('lineRouteEditor', () => {
  it('maps line row to route draft and back', () => {
    const draft = lineRowToRouteDraft(sampleLine);
    expect(draft.station_ids).toEqual([10, 20]);
    const updated = applyRouteDraftToLine(sampleLine, { ...draft, station_ids: [10, 30, 20] });
    expect(updated.station_ids).toEqual([10, 30, 20]);
    expect(updated.start_station).toBe(10);
    expect(updated.end_station).toBe(20);
  });

  it('moves and removes stations on a line draft', () => {
    const moved = applyLineStationMove({ ...sampleLine, station_ids: [10, 30, 20] }, 2, -1);
    expect(moved.station_ids).toEqual([10, 20, 30]);
    const removed = removeLineStation(moved, 1);
    expect(removed.station_ids).toEqual([10, 30]);
  });
});
