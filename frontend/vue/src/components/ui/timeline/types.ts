import type { TimelineStopBase } from '../../../shared/timelineStop';

export type MrtTimelineStop = TimelineStopBase;

export type StopPosition = 'first' | 'middle' | 'last' | 'only';

export type LineSegment = 'none' | 'down' | 'through' | 'up';

export type TimelineRow =
  | { kind: 'stop'; stop: MrtTimelineStop; position: StopPosition; key: string }
  | { kind: 'toggle' };
