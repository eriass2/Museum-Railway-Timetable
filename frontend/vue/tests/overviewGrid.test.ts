import { describe, expect, it } from 'vitest';
import {
  buildHighlightStripeSpans,
  buildOverviewGridTracks,
  overviewGridMinWidth,
  overviewGridTemplateColumns,
  splitOverviewRowSegments,
} from '../src/utils/overviewGrid';
import type { TimetableOverviewColumn, TimetableOverviewRow } from '../src/types/timetableOverview';

function column(overrides: Partial<TimetableOverviewColumn> = {}): TimetableOverviewColumn {
  return {
    serviceNumber: '71',
    trainTypeName: 'Ångtåg',
    trainTypeSlug: 'angtag',
    iconKey: 'steam',
    isSpecial: false,
    specialName: '',
    highlightColor: '',
    ...overrides,
  };
}

describe('overviewGrid tracks', () => {
  it('inserts a highlight stripe before marked departures', () => {
    const tracks = buildOverviewGridTracks([
      column({ serviceNumber: '71' }),
      column({
        serviceNumber: '93',
        isSpecial: true,
        specialName: "Thun's-expressen",
        highlightColor: '#fff9c4',
      }),
    ]);

    expect(tracks).toEqual([
      { kind: 'train', columnIndex: 0 },
      {
        kind: 'highlight',
        label: "Thun's-expressen",
        color: '#fff9c4',
        columnIndex: 1,
      },
      { kind: 'train', columnIndex: 1 },
    ]);
  });

  it('builds wider grid templates when highlight stripes exist', () => {
    const cols = [
      column({ serviceNumber: '71' }),
      column({ serviceNumber: '93', specialName: 'Express', highlightColor: '#fff9c4' }),
    ];

    expect(overviewGridTemplateColumns(cols)).toContain('1.15rem');
    expect(parseFloat(overviewGridMinWidth(cols))).toBeGreaterThan(10.5 + 4.1 * 2);
  });

  it('merges highlight stripes per segment around train change', () => {
    const rows: TimetableOverviewRow[] = [
      { kind: 'from', label: 'Från A', cells: [{ text: '10:00' }, { text: '11:00' }] },
      { kind: 'station', label: 'B', cells: [{ text: '10:10' }, { text: '11:10' }] },
      { kind: 'trainChange', label: 'Tågbyte:', cells: [{ vehicles: [] }, { vehicles: [] }] },
      { kind: 'station', label: 'C', cells: [{ text: '10:20' }, { text: '11:20' }] },
      { kind: 'to', label: 'Till D', cells: [{ text: '10:30' }, { text: '11:30' }] },
    ];
    const tracks = buildOverviewGridTracks([
      column({ serviceNumber: '71' }),
      column({ serviceNumber: '93', specialName: "Thun's-expressen", highlightColor: '#fff9c4' }),
    ]);

    const spans = buildHighlightStripeSpans(rows, tracks);
    expect(spans).toHaveLength(2);
    expect(spans[0]).toMatchObject({ rowIndex: 0, rowSpan: 2, label: "Thun's-expressen" });
    expect(spans[1]).toMatchObject({ rowIndex: 3, rowSpan: 2 });
  });

  it('splitOverviewRowSegments breaks at transfer rows', () => {
    const rows: TimetableOverviewRow[] = [
      { kind: 'from', label: 'A', cells: [] },
      { kind: 'trainChange', label: 'Byte', cells: [] },
      { kind: 'to', label: 'B', cells: [] },
    ];
    expect(splitOverviewRowSegments(rows)).toEqual([[0], [2]]);
  });
});
