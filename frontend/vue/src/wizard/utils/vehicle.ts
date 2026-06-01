import type { JourneyLeg } from '../../shared/journey';
import {
  normalizeTrainTypeIconKey,
  trainTypeIconUrl as iconUrlForKey,
} from '../../shared/trainTypeIcons';
import type { MrtVehicleItem } from '../../components/ui/types';
import type { WizardCfg } from './wizardCfgTypes';
import { cfgStr } from './wizardLabels';

export function trainIconKey(
  label: string,
  slug: string,
  iconKeyArg: string,
  cfg: WizardCfg,
): string {
  if (iconKeyArg) {
    return normalizeTrainTypeIconKey(iconKeyArg);
  }
  const slugMap = cfg.trainTypeSlugIcons ?? {};
  const slugLower = slug.toLowerCase();
  if (slugLower && slugMap[slugLower]) {
    return normalizeTrainTypeIconKey(slugMap[slugLower]);
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
  return iconUrlForKey(cfg.trainTypeIcons ?? {}, kind);
}

function legServiceNumber(leg: JourneyLeg): string {
  if (leg.service_number) {
    return leg.service_number;
  }
  if (leg.service_id) {
    return String(leg.service_id);
  }
  return '';
}

function legTowardsSuffix(destination: string, cfg?: WizardCfg): string {
  const dest = destination.trim();
  if (!dest) {
    return '';
  }
  return cfgStr(cfg ?? {}, 'towards', 'mot %s').replace('%s', dest);
}

export function legVehicleLabel(leg: JourneyLeg, cfg?: WizardCfg): string {
  const train = leg.train_type?.trim() || cfgStr(cfg ?? {}, 'defaultTrainType', 'Tåg');
  const number = legServiceNumber(leg);
  const towards = legTowardsSuffix(leg.destination || '', cfg);
  if (number && towards) {
    return `${train} ${number} ${towards}`;
  }
  if (number) {
    return `${train} ${number}`;
  }
  if (towards) {
    return `${train} ${towards}`;
  }
  return train;
}

export function legVehicleKind(leg: JourneyLeg, cfg: WizardCfg): string {
  return trainIconKey(
    legVehicleLabel(leg, cfg),
    leg.train_type_slug || '',
    leg.train_type_icon || '',
    cfg,
  );
}

export function legToVehicleItem(leg: JourneyLeg, cfg: WizardCfg): MrtVehicleItem {
  const kind = legVehicleKind(leg, cfg);
  return {
    kind,
    label: legVehicleLabel(leg, cfg),
    iconUrl: trainIconUrl(kind, cfg),
  };
}

export function legsToVehicleItems(legs: JourneyLeg[], cfg: WizardCfg): MrtVehicleItem[] {
  return legs.map((leg) => legToVehicleItem(leg, cfg));
}
