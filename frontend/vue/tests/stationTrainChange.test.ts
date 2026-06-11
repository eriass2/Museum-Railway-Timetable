import { describe, expect, it } from 'vitest';
import {
  appendTrainChangeEntry,
  trainChangeEntriesToMap,
  trainChangeEntryCount,
  trainChangeMapToEntries,
  validateTrainChangeEntries,
} from '../src/admin/utils/stations-routes/stationTrainChange';

describe('stationTrainChange', () => {
  it('converts map to entries and back', () => {
    const map = {
      '71': { typeName: 'Dieseltåg', serviceNumber: '61' },
    };
    expect(trainChangeMapToEntries(map)).toEqual([
      { from_service: '71', type_name: 'Dieseltåg', to_service: '61' },
    ]);
    expect(trainChangeEntriesToMap(trainChangeMapToEntries(map))).toEqual(map);
  });

  it('skips incomplete rows when building map', () => {
    expect(
      trainChangeEntriesToMap([
        { from_service: '71', type_name: '', to_service: '61' },
        { from_service: '', type_name: 'Buss', to_service: '12' },
      ]),
    ).toEqual({});
  });

  it('counts configured entries', () => {
    const station = { train_change_map: {} as Record<string, { typeName: string; serviceNumber: string }> };
    expect(trainChangeEntryCount(station.train_change_map)).toBe(0);
    appendTrainChangeEntry(station);
    station.train_change_map!['71'] = { typeName: 'Dieseltåg', serviceNumber: '61' };
    expect(trainChangeEntryCount(station.train_change_map)).toBe(1);
  });

  it('validates incomplete and duplicate rows', () => {
    const result = validateTrainChangeEntries([
      { from_service: '71', type_name: 'Dieseltåg', to_service: '61' },
      { from_service: '72', type_name: '', to_service: '62' },
      { from_service: '71', type_name: 'Dieseltåg', to_service: '63' },
      { from_service: '74', type_name: 'Dieseltåg', to_service: '61' },
    ]);
    expect(result.incompleteRows).toEqual([2]);
    expect(result.duplicateFromServices).toEqual(['71']);
    expect(result.duplicateToServices).toEqual(['61']);
  });
});
