import type { MaybeRefOrGetter } from 'vue';
import { toValue } from 'vue';
import type { WizardCfg } from './wizardCfgTypes';
import { cfgStr } from './wizardLabels';

export type WizardCalendarLegendItem = {
  swatchClass: string;
  label: string;
};

export function wizardCalendarLegendItems(
  cfg: MaybeRefOrGetter<WizardCfg>,
): WizardCalendarLegendItem[] {
  const cfgVal = toValue(cfg);
  return [
    {
      swatchClass: 'mrt-legend-swatch--ok',
      label: cfgStr(cfgVal, 'legendOk', 'Trafik för din resa'),
    },
    {
      swatchClass: 'mrt-legend-swatch--traffic',
      label: cfgStr(cfgVal, 'legendTraffic', 'Trafik, ej din resa'),
    },
    {
      swatchClass: 'mrt-legend-swatch--none',
      label: cfgStr(cfgVal, 'legendNone', 'Ingen trafik'),
    },
  ];
}
