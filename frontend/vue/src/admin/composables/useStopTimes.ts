import { ref, type Ref } from 'vue';
import { getStopTimes, saveStopTimes } from '../api/adminRest';
import type { StopTimeRow } from '../types';
import { adminConfig } from '../types';
import { adminStr } from '../utils/adminLabels';
import { stopTimesToApiPayload } from '../utils/stopTimesPayload';

export function useStopTimes(serviceId: Ref<number>) {
  const cfg = adminConfig();
  const stations = ref<StopTimeRow[]>([]);
  const loading = ref(false);
  const error = ref('');
  const message = ref('');

  async function load() {
    const id = serviceId.value;
    if (!id) {
      stations.value = [];
      loading.value = false;
      return;
    }
    loading.value = true;
    error.value = '';
    message.value = '';
    try {
      const res = await getStopTimes(id);
      stations.value = res.stations;
    } catch (e) {
      error.value = e instanceof Error ? e.message : adminStr(cfg, 'genericError');
    } finally {
      loading.value = false;
    }
  }

  async function save(explicit = true) {
    const id = serviceId.value;
    if (!id || (!cfg.canManage && !cfg.canOperate)) {
      return;
    }
    error.value = '';
    try {
      const res = await saveStopTimes(id, stopTimesToApiPayload(stations.value), !explicit);
      stations.value = res.stations;
      message.value = adminStr(cfg, 'stopTimesSaved');
    } catch (e) {
      error.value = e instanceof Error ? e.message : adminStr(cfg, 'saveFailed');
    }
  }

  async function persistRows(id: number, rows: StopTimeRow[], quickEdit = false): Promise<StopTimeRow[]> {
    error.value = '';
    const res = await saveStopTimes(id, stopTimesToApiPayload(rows), quickEdit);
    return res.stations;
  }

  return { stations, loading, error, message, load, save, persistRows };
}
