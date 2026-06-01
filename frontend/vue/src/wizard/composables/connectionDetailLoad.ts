import { unref, type MaybeRef } from 'vue';
import { mrtRestRequest } from '../../api/mrtRest';
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

type LoadParams = {
  config: WizardVueConfig;
  cfg: WizardCfg;
  connection: JourneyConnection;
  legFrom: MaybeRef<number>;
  legTo: MaybeRef<number>;
};

async function fetchConnectionLegDetail(
  params: LoadParams,
  leg: JourneyLeg,
): Promise<ConnectionDetailPayload | null> {
  const from = leg.from_station_id || unref(params.legFrom);
  const to = leg.to_station_id || unref(params.legTo);
  const res = await mrtRestRequest<ConnectionDetailPayload>(
    params.config,
    'mrt_journey_connection_detail',
    { from_station: from, to_station: to, service_id: leg.service_id },
  );
  return res.success && res.data ? res.data : null;
}

function segmentFromDetailPayload(
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

export async function loadConnectionDetailSegments(params: LoadParams): Promise<LegSegment[]> {
  const legs = connectionLegs(params.connection);
  const legTpl = cfgStr(params.cfg, 'legSegmentLabel', 'Delsträcka %d');
  const out: LegSegment[] = [];

  if (legs.length > 1) {
    for (let i = 0; i < legs.length; i++) {
      const leg = legs[i];
      const data = await fetchConnectionLegDetail(params, leg);
      if (!data) {
        return [];
      }
      out.push(segmentFromDetailPayload(data, legTpl.replace('%d', String(i + 1)), leg));
    }
    return out;
  }

  const data = await fetchConnectionLegDetail(params, legs[0]);
  if (!data) {
    return [];
  }
  out.push(segmentFromDetailPayload(data, '', legs[0]));
  return out;
}

export function connectionTransferLabel(
  connection: JourneyConnection,
  cfg: WizardCfg,
): string {
  const wait = connection.transfer_wait_minutes;
  if (wait !== null && wait !== undefined && !Number.isNaN(Number(wait))) {
    return cfgStr(cfg, 'transferWait', '%d min byte').replace('%d', String(wait));
  }
  return cfgStr(cfg, 'transferTrip', 'Byte');
}
