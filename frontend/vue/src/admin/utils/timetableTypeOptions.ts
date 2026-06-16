import type { AdminClientConfig } from '../types';
import { adminStr } from './adminLabels';

export type TimetableTypeOption = {
  value: string;
  label: string;
};

export function buildTimetableTypeOptions(cfg: AdminClientConfig): readonly TimetableTypeOption[] {
  return [
    { value: '', label: adminStr(cfg, 'editorTypeNone') },
    { value: 'green', label: adminStr(cfg, 'editorTypeGreen') },
    { value: 'yellow', label: adminStr(cfg, 'editorTypeYellow') },
    { value: 'red', label: adminStr(cfg, 'editorTypeRed') },
    { value: 'orange', label: adminStr(cfg, 'editorTypeOrange') },
  ] as const;
}
