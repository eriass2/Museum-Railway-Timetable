import { computed, ref, type MaybeRef, unref } from 'vue';
import { mrtPost } from '../../api/mrtApi';
import type { WizardVueConfig } from '../../config/types';
import type {
  ConnectionDetailPayload,
  JourneyConnection,
  JourneyLeg,
  TimelineStop,
} from '../types';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStr } from '../utils/wizardLabels';
import { connectionLegs } from '../utils/connection';

export type LegSegment = {
  title: string;
  stops: TimelineStop[];
  notice: string;
  leg?: JourneyLeg;
};

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
  const legTpl = computed(() => cfgStr(cfg.value, 'legSegmentLabel', 'Delsträcka %d'));

  async function fetchLegDetail(leg: JourneyLeg): Promise<ConnectionDetailPayload | null> {
    const from = leg.from_station_id || unref(params.legFrom);
    const to = leg.to_station_id || unref(params.legTo);
    const res = await mrtPost<ConnectionDetailPayload>(
      params.config,
      'mrt_journey_connection_detail',
      { from_station: from, to_station: to, service_id: leg.service_id },
    );
    return res.success && res.data ? res.data : null;
  }

  function segmentFromPayload(
    data: ConnectionDetailPayload,
    title: string,
    leg?: JourneyLeg,
  ): LegSegment {
    return {
      title,
      stops: data.detail?.stops || [],
      notice: data.notice || '',
      leg,
    };
  }

  async function loadMultiLegSegments(): Promise<boolean> {
    for (let i = 0; i < legs.value.length; i++) {
      const leg = legs.value[i];
      const data = await fetchLegDetail(leg);
      if (!data) {
        return false;
      }
      segments.value.push(
        segmentFromPayload(data, legTpl.value.replace('%d', String(i + 1)), leg),
      );
    }
    return true;
  }

  async function loadSingleSegment(): Promise<boolean> {
    const data = await fetchLegDetail(legs.value[0]);
    if (!data) {
      return false;
    }
    segments.value.push(segmentFromPayload(data, '', legs.value[0]));
    return true;
  }

  async function loadDetail(): Promise<void> {
    if (loaded.value) {
      return;
    }
    loading.value = true;
    error.value = '';
    segments.value = [];

    const ok = isMulti.value ? await loadMultiLegSegments() : await loadSingleSegment();
    loading.value = false;
    if (!ok) {
      error.value = cfgStr(cfg.value, 'errorGeneric', 'Något gick fel.');
      return;
    }
    loaded.value = true;
  }

  function transferLabel(): string {
    const wait = params.connection.transfer_wait_minutes;
    if (wait !== null && wait !== undefined && !Number.isNaN(Number(wait))) {
      return cfgStr(cfg.value, 'transferWait', '%d min byte').replace('%d', String(wait));
    }
    return cfgStr(cfg.value, 'transferTrip', 'Byte');
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
    transferLabel,
    ensureLoaded,
  };
}
