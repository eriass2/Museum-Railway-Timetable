import type { StopTimeMode } from '../../types';

export const STOP_TIME_MODES: StopTimeMode[] = ['none', 'scheduled', 'on_request'];

export const STOP_TIME_MODE_LABEL_KEYS: Record<StopTimeMode, string> = {
  none: 'stopTimeModeNone',
  scheduled: 'stopTimeModeScheduled',
  on_request: 'stopTimeModeOnRequest',
};
