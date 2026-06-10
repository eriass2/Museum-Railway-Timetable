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
import { isCancelledNotice } from '../../shared/cancelledNotice';

export type LegSegment = {
  title: string;
  stops: TimelineStop[];
  notice: string;
  isCancelled?: boolean;
  leg?: JourneyLeg;
};

type LoadParams = {
  config: WizardVueConfig;
  cfg: WizardCfg;
  connection: JourneyConnection;
  legFrom: MaybeRef<number>;
  legTo: MaybeRef<number>;
  dateYmd: MaybeRef<string>;
};

async function fetchConnectionLegDetail(
  params: LoadParams,
  leg: JourneyLeg,
): Promise<ConnectionDetailPayload | null> {
  const from = leg.from_station_id || unref(params.legFrom);
  const to = leg.to_station_id || unref(params.legTo);
  const res = await mrtRestRequest<ConnectionDetailPayload>(params.config, {
    method: 'POST',
    path: 'journey/connection-detail',
    body: {
      from_station: from,
      to_station: to,
      service_id: leg.service_id,
      date: unref(params.dateYmd) || undefined,
    },
  });
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
    isCancelled: data.is_cancelled === true || isCancelledNotice(data.notice || ''),
    leg,
  };
}

export async function loadConnectionDetailSegments(params: LoadParams): Promise<LegSegment[]> {
  const legs = connectionLegs(params.connection);
  const legTpl = cfgStr(params.cfg, 'legSegmentLabel', 'Delsträcka %d');

  if (legs.length > 1) {
    const details = await Promise.all(legs.map((leg) => fetchConnectionLegDetail(params, leg)));
    if (details.some((data) => !data)) {
      return [];
    }
    return details.map((data, i) =>
      segmentFromDetailPayload(data!, legTpl.replace('%d', String(i + 1)), legs[i]),
    );
  }

  const data = await fetchConnectionLegDetail(params, legs[0]);
  if (!data) {
    return [];
  }
  return [segmentFromDetailPayload(data, '', legs[0])];
}
