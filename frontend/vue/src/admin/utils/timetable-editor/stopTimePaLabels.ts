import type { AdminClientConfig } from '../../types';
import { adminStr } from '../adminLabels';

export type StopTimePaKind = 'pickup' | 'dropoff';

export function stopTimePaShortLabel(cfg: AdminClientConfig, kind: StopTimePaKind): string {
  return kind === 'pickup'
    ? adminStr(cfg, 'stopTimesColPickup')
    : adminStr(cfg, 'stopTimesColDropoff');
}

export function stopTimePaTooltip(cfg: AdminClientConfig, kind: StopTimePaKind): string {
  return kind === 'pickup'
    ? adminStr(cfg, 'stopTimesPickupLabel')
    : adminStr(cfg, 'stopTimesDropoffLabel');
}
