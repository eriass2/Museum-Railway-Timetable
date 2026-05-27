import type { JourneyLeg } from '../types';
import type { WizardCfg } from './wizardLabels';

export function trainIconKey(
  label: string,
  slug: string,
  iconKeyArg: string,
  cfg: WizardCfg,
): string {
  if (iconKeyArg) {
    return iconKeyArg;
  }
  const slugMap = (cfg.trainTypeSlugIcons || {}) as Record<string, string>;
  const slugLower = slug.toLowerCase();
  if (slugLower && slugMap[slugLower]) {
    return slugMap[slugLower];
  }
  const s = label.toLowerCase();
  if (s.includes('rälsbuss') || s.includes('ralsbuss') || s.includes('railbus')) {
    return 'railbus';
  }
  if (s === 'buss' || slugLower === 'buss') {
    return 'bus';
  }
  if (slugLower === 'ang-diesel' || (s.includes('ång') && s.includes('diesel'))) {
    return 'diesel';
  }
  if (s.includes('ång') || s.includes('steam') || slugLower === 'angtag') {
    return 'steam';
  }
  return 'diesel';
}

export function trainIconUrl(kind: string, cfg: WizardCfg): string {
  const icons = (cfg.trainTypeIcons || {}) as Record<string, string>;
  return icons[kind] || icons.diesel || '';
}

export function legVehicleLabel(leg: JourneyLeg): string {
  const service = leg.service_name || leg.service_number || (leg.service_id ? String(leg.service_id) : '');
  const train = leg.train_type || '';
  if (train && service) {
    return `${train} ${service}`;
  }
  return train || service || 'Tåg';
}

export function legVehicleKind(leg: JourneyLeg, cfg: WizardCfg): string {
  return trainIconKey(
    legVehicleLabel(leg),
    leg.train_type_slug || '',
    leg.train_type_icon || '',
    cfg,
  );
}
