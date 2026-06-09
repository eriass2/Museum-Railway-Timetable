import type { PriceTableLabels } from '../../shared/priceLabels';
import { formatPriceZoneSpan } from '../../shared/priceZoneLabels';
import type { WizardCfg } from './wizardCfgTypes';
import { cfgRecord, cfgStr } from './wizardLabels';

export function priceTableLabelsFromCfg(cfg: WizardCfg, zones: number, showZoneCount: boolean): PriceTableLabels {
  let titleSuffix = '';
  if (showZoneCount) {
    titleSuffix = `(${formatPriceZoneSpan(zones)})`;
  }
  return {
    title: cfgStr(cfg, 'priceTitle', 'Priser'),
    titleSuffix,
    typeColumnSr: cfgStr(cfg, 'priceTableTypeColumn', ''),
    note: cfgStr(cfg, 'priceNote', ''),
    dash: cfgStr(cfg, 'priceDash', '—'),
    tickets: cfgRecord(cfg, 'priceTickets'),
    categories: cfgRecord(cfg, 'priceCategories'),
  };
}
