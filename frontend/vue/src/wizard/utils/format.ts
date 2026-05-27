import type { WizardCfg } from './wizardCfgTypes';
import { cfgStr } from './wizardLabels';

export function formatTripClock(time: string): string {
  return time ? time.replace(':', '.') : '—';
}

export function formatDuration(minutes: number | undefined, cfg: WizardCfg): string {
  const m = Number(minutes);
  if (!Number.isFinite(m) || m < 0) {
    return '';
  }
  if (m >= 60) {
    const h = Math.floor(m / 60);
    const rest = m % 60;
    return rest ? `${h} tim ${rest} min` : `${h} tim`;
  }
  return cfgStr(cfg, 'durationMinutes', '%d min').replace('%d', String(m));
}

export function isWarningNotice(notice: string): boolean {
  const n = notice.toLowerCase();
  return n.includes('brand') || n.includes('ersatt') || n.includes('varning');
}
