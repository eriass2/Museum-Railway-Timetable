import type { AdminClientConfig } from '../../types';
import { adminStr } from '../adminLabels';

export type TimetableEditorTabKey =
  | 'dates'
  | 'grid'
  | 'trips'
  | 'stoptimes'
  | 'deviations'
  | 'preview';

export type TimetableEditorTabEntry = readonly [TimetableEditorTabKey, string];

/** Desktop editor tab labels keyed by tab id. */
export function buildTimetableEditorDesktopTabs(cfg: AdminClientConfig): TimetableEditorTabEntry[] {
  return [
    ['dates', adminStr(cfg, 'editorTabDates')],
    ['grid', adminStr(cfg, 'editorTabGrid')],
    ['trips', adminStr(cfg, 'editorTabTrips')],
    ['stoptimes', adminStr(cfg, 'editorTabStoptimes')],
    ['deviations', adminStr(cfg, 'editorTabDeviations')],
    ['preview', adminStr(cfg, 'editorTabPreview')],
  ];
}
