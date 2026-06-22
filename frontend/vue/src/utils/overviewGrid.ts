import type { TimetableOverviewColumn, TimetableOverviewRow } from '../types/timetableOverview';

export { trainTypeIconUrl } from '../shared/trainTypeIcons';

export type OverviewGridTrack =
  | { kind: 'highlight'; label: string; color: string; columnIndex: number }
  | { kind: 'train'; columnIndex: number };

export function isTimeRow(
  row: TimetableOverviewRow,
): row is Extract<TimetableOverviewRow, { cells: { text: string }[] }> {
  return (
    row.kind !== 'trainChangeType' &&
    row.kind !== 'trainChangeNumber' &&
    row.kind !== 'busConnection'
  );
}

export function isTransferRow(
  row: TimetableOverviewRow,
): row is Extract<
  TimetableOverviewRow,
  { kind: 'trainChangeType' | 'trainChangeNumber' | 'busConnection' }
> {
  return (
    row.kind === 'trainChangeType' ||
    row.kind === 'trainChangeNumber' ||
    row.kind === 'busConnection'
  );
}

export function isBusRow(row: TimetableOverviewRow): boolean {
  return row.kind === 'busDeparture' || row.kind === 'busArrival';
}

export function overviewRowClass(row: TimetableOverviewRow, rowIndex = 0): string {
  const classes: string[] = [];
  if (row.kind === 'from' || row.kind === 'departure') {
    classes.push('mrt-ov-grid-row--from');
  } else if (row.kind === 'to' || row.kind === 'arrival') {
    classes.push('mrt-ov-grid-row--to');
  } else if (row.kind === 'busDeparture' || row.kind === 'busArrival') {
    classes.push('mrt-ov-grid-row--bus');
  } else if (row.kind === 'trainChangeType') {
    classes.push('mrt-ov-grid-row--transfer', 'mrt-ov-grid-row--transfer-type');
  } else if (row.kind === 'trainChangeNumber') {
    classes.push('mrt-ov-grid-row--transfer', 'mrt-ov-grid-row--transfer-number');
  } else if (row.kind === 'busConnection') {
    classes.push('mrt-ov-grid-row--transfer');
  } else if (row.kind === 'station' && rowIndex % 2 === 0) {
    classes.push('mrt-ov-grid-row--alt');
  }
  return classes.join(' ');
}

export type HighlightStripeSpan = {
  trackIndex: number;
  label: string;
  color: string;
  rowIndex: number;
  rowSpan: number;
};

export function buildOverviewGridTracks(columns: TimetableOverviewColumn[]): OverviewGridTrack[] {
  const tracks: OverviewGridTrack[] = [];
  columns.forEach((col, columnIndex) => {
    if (col.specialName) {
      tracks.push({
        kind: 'highlight',
        label: col.specialName,
        color: col.highlightColor || '#fff9c4',
        columnIndex,
      });
    }
    tracks.push({ kind: 'train', columnIndex });
  });
  return tracks;
}

export type OverviewGridDensity = {
  stationW: string;
  colMin: string;
  colMax: string;
  highlightW: string;
  numSize: string;
};

/** Tighter columns when many trips + highlight stripes would overflow typical viewports. */
export function overviewGridDensity(trackCount: number): OverviewGridDensity {
  if (trackCount >= 8) {
    return {
      stationW: '8.25rem',
      colMin: '2.9rem',
      colMax: '3.35rem',
      highlightW: '0.9rem',
      numSize: '0.85rem',
    };
  }
  if (trackCount >= 6) {
    return {
      stationW: '9rem',
      colMin: '3rem',
      colMax: '3.65rem',
      highlightW: '1rem',
      numSize: '0.92rem',
    };
  }
  return {
    stationW: '10.5rem',
    colMin: '3.35rem',
    colMax: '4.1rem',
    highlightW: '1.15rem',
    numSize: '1rem',
  };
}

export function overviewGridMinWidth(columns: TimetableOverviewColumn[]): string {
  const tracks = buildOverviewGridTracks(columns);
  const density = overviewGridDensity(tracks.length);
  let rem = parseFloat(density.stationW);
  for (const track of tracks) {
    rem += track.kind === 'highlight' ? parseFloat(density.highlightW) : parseFloat(density.colMax);
  }
  return `${rem}rem`;
}

export function overviewGridTemplateColumns(columns: TimetableOverviewColumn[]): string {
  const parts = ['var(--mrt-ov-station-w)'];
  for (const track of buildOverviewGridTracks(columns)) {
    parts.push(
      track.kind === 'highlight'
        ? 'minmax(0.85rem, var(--mrt-ov-highlight-w, 1.15rem))'
        : 'minmax(var(--mrt-ov-col-min), var(--mrt-ov-col-max, 4.1rem))',
    );
  }
  return parts.join(' ');
}

export function overviewHighlightStripeStyle(color: string): Record<string, string> {
  return {
    backgroundColor: color,
    '--mrt-ov-cell-highlight': color,
  };
}

export function overviewGridStyle(columns: TimetableOverviewColumn[]): Record<string, string> {
  const tracks = buildOverviewGridTracks(columns);
  const density = overviewGridDensity(tracks.length);
  return {
    gridTemplateColumns: overviewGridTemplateColumns(columns),
    '--mrt-ov-grid-min': overviewGridMinWidth(columns),
    '--mrt-ov-station-w': density.stationW,
    '--mrt-ov-col-min': density.colMin,
    '--mrt-ov-col-max': density.colMax,
    '--mrt-ov-highlight-w': density.highlightW,
    '--mrt-ov-num-size': density.numSize,
  };
}

/** 1-based grid column for a track (column 1 = station). */
export function overviewGridCellColumn(trackIndex: number): number {
  return trackIndex + 2;
}

/**
 * Split timetable rows into segments separated by transfer rows (tågbyte).
 *
 * @return array of row indices per segment
 */
export function splitOverviewRowSegments(rows: TimetableOverviewRow[]): number[][] {
  const segments: number[][] = [];
  let current: number[] = [];
  rows.forEach((row, index) => {
    if (isTransferRow(row)) {
      if (current.length > 0) {
        segments.push(current);
        current = [];
      }
      return;
    }
    current.push(index);
  });
  if (current.length > 0) {
    segments.push(current);
  }
  return segments;
}

export function buildHighlightStripeSpans(
  rows: TimetableOverviewRow[],
  tracks: OverviewGridTrack[],
): HighlightStripeSpan[] {
  const segments = splitOverviewRowSegments(rows);
  const spans: HighlightStripeSpan[] = [];
  tracks.forEach((track, trackIndex) => {
    if (track.kind !== 'highlight') {
      return;
    }
    segments.forEach((indices) => {
      if (indices.length === 0) {
        return;
      }
      spans.push({
        trackIndex,
        label: track.label,
        color: track.color,
        rowIndex: indices[0],
        rowSpan: indices.length,
      });
    });
  });
  return spans;
}

export function highlightStripeSpanAt(
  spans: HighlightStripeSpan[],
  rowIndex: number,
  trackIndex: number,
): HighlightStripeSpan | null {
  return spans.find((s) => s.trackIndex === trackIndex && s.rowIndex === rowIndex) ?? null;
}

export function highlightStripeSpanStyle(span: HighlightStripeSpan): Record<string, string> {
  const gridRowStart = span.rowIndex + 3;
  return {
    gridColumn: String(overviewGridCellColumn(span.trackIndex)),
    gridRow: `${gridRowStart} / span ${span.rowSpan}`,
    ...overviewHighlightStripeStyle(span.color),
  };
}

export function overviewGridCellStyle(trackIndex: number): Record<string, string> {
  return { gridColumn: String(overviewGridCellColumn(trackIndex)) };
}

export function overviewStationColumnStyle(): Record<string, string> {
  return { gridColumn: '1' };
}

export function overviewHeadRowStyle(row: 1 | 2): Record<string, string> {
  return { gridRow: String(row) };
}
