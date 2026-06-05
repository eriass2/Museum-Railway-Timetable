import { computed, ref, unref, type MaybeRef } from 'vue';
import type { WizardVueConfig } from '../../config/types';
import { buildTransferLabel, stationTitleLookup } from '../../shared/connectionLegDisplay';
import type { JourneyConnection } from '../../shared/journey';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStr, transferLabelStringsFromCfg } from '../utils/wizardLabels';
import { connectionLegs } from '../utils/connection';
import { loadConnectionDetailSegments, type LegSegment } from './connectionDetailLoad';

export type { LegSegment } from './connectionDetailLoad';

type DetailParams = {
  config: WizardVueConfig;
  cfg: MaybeRef<WizardCfg>;
  connection: JourneyConnection;
  legFrom: MaybeRef<number>;
  legTo: MaybeRef<number>;
};

export function useConnectionDetail(params: DetailParams) {
  const cfg = computed(() => unref(params.cfg));
  const loading = ref(false);
  const error = ref('');
  const segments = ref<LegSegment[]>([]);
  const loaded = ref(false);

  const legs = computed(() => connectionLegs(params.connection));
  const isMulti = computed(() => legs.value.length > 1);
  const stationTitle = computed(() =>
    stationTitleLookup(params.config.stations || []),
  );

  async function loadDetail(): Promise<void> {
    if (loaded.value) {
      return;
    }
    loading.value = true;
    error.value = '';
    segments.value = [];

    const next = await loadConnectionDetailSegments({
      config: params.config,
      cfg: cfg.value,
      connection: params.connection,
      legFrom: params.legFrom,
      legTo: params.legTo,
    });
    loading.value = false;
    if (!next.length) {
      error.value = cfgStr(cfg.value, 'errorGeneric', 'Något gick fel. Försök igen.');
      return;
    }
    segments.value = next;
    loaded.value = true;
  }

  function transferLabelAt(segmentIndex: number): string {
    const legList = legs.value;
    if (segmentIndex < 0 || segmentIndex >= legList.length - 1) {
      return '';
    }
    return buildTransferLabel(
      legList[segmentIndex],
      legList[segmentIndex + 1],
      params.connection,
      stationTitle.value,
      transferLabelStringsFromCfg(params.cfg),
    );
  }

  async function ensureLoaded(): Promise<void> {
    if (!loaded.value) {
      await loadDetail();
    }
  }

  return {
    cfg,
    loading,
    error,
    segments,
    loaded,
    isMulti,
    transferLabelAt,
    ensureLoaded,
  };
}
