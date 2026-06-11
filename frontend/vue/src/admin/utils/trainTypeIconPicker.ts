import { trainTypeIconLabel } from '../../shared/trainTypeIcons';
import { adminStr } from './adminLabels';
import type { AdminClientConfig } from '../types';

export type TrainTypeIconOption = {
  key: string;
  label: string;
};

/** Map icon keys to localized picker labels. */
export function buildTrainTypeIconOptions(
  cfg: AdminClientConfig,
  iconKeys: string[],
): TrainTypeIconOption[] {
  return iconKeys.map((key) => ({
    key,
    label: trainTypeIconLabel(key, (labelKey) => adminStr(cfg, labelKey)),
  }));
}
