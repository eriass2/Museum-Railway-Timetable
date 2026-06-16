import type { TimetableServiceRow } from '../../types';

export type TimetableServiceEditRow = TimetableServiceRow & {
  highlight_label?: string;
  highlight_color?: string;
  highlight_note?: string;
};

export type StoptimesPanelView = 'list' | 'detail';

export type TimetableEditorTab = 'dates' | 'grid' | 'trips' | 'stoptimes' | 'deviations' | 'preview';

export const TIMETABLE_EDITOR_TABS: TimetableEditorTab[] = [
  'dates',
  'grid',
  'trips',
  'stoptimes',
  'deviations',
  'preview',
];

export function parseTimetableEditorTab(value: unknown): TimetableEditorTab | null {
  if (typeof value !== 'string') {
    return null;
  }
  return TIMETABLE_EDITOR_TABS.includes(value as TimetableEditorTab)
    ? (value as TimetableEditorTab)
    : null;
}
