import { parseTimeLabelCaPrefix } from '../../../shared/parseTimeLabel';
import type { TimeLabelCaParts } from '../../../shared/parseTimeLabel';
import { stopShowsOnRequestInfo } from '../../../shared/stopTimeFootnotes';
import type { MrtTimelineStop, StopPosition, TimelineRow } from './types';

export type StopDisplayRow = {
  kind: 'stop';
  key: string;
  position: StopPosition;
  timeParts: TimeLabelCaParts;
  stationTitle: string;
  showInfo: boolean;
};

export type DisplayTimelineRow = StopDisplayRow | { kind: 'toggle' };

export function mapTimelineDisplay(
  rows: TimelineRow[],
  formatTime: (stop: MrtTimelineStop) => string,
): DisplayTimelineRow[] {
  return rows.map((row) => {
    if (row.kind === 'toggle') {
      return row;
    }

    return {
      kind: 'stop',
      key: row.key,
      position: row.position,
      timeParts: parseTimeLabelCaPrefix(formatTime(row.stop)),
      stationTitle: row.stop.station_title ?? '',
      showInfo: stopShowsOnRequestInfo(row.stop),
    };
  });
}
