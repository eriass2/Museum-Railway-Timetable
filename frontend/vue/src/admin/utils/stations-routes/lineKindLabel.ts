import type { LineKind } from '../../types';

export function lineKindLabelKey(kind: LineKind): string {
  if (kind === 'main') {
    return 'stationsLineKindMain';
  }
  if (kind === 'branch') {
    return 'stationsLineKindBranch';
  }
  if (kind === 'pattern') {
    return 'stationsLineKindPattern';
  }
  return '';
}
