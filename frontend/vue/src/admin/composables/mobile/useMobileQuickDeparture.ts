import { onMounted, ref, watch } from 'vue';
import { getStopTimes, quickDeparture } from '../../api/adminRest';
import type { TimetableServiceRow } from '../../types';
import { adminConfig } from '../../types';
import { adminErrorMessage, adminStr } from '../../utils/adminLabels';

export function useMobileQuickDeparture(
  services: () => TimetableServiceRow[],
  canEdit: () => boolean,
  onSaved: (message: string) => void,
) {
  const cfg = adminConfig();
  const serviceId = ref(0);
  const departure = ref('');
  const firstStopName = ref('');
  const loading = ref(false);
  const error = ref('');

  async function loadFirstStop() {
    if (!serviceId.value) {
      firstStopName.value = '';
      departure.value = '';
      return;
    }
    loading.value = true;
    error.value = '';
    try {
      const data = await getStopTimes(serviceId.value);
      const first = data.stations[0];
      firstStopName.value = first?.name || '—';
      departure.value = first?.departure_time || '';
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'mobileStopTimesLoadFailed');
    } finally {
      loading.value = false;
    }
  }

  watch(serviceId, () => {
    void loadFirstStop();
  });

  onMounted(() => {
    const list = services();
    if (list.length === 1) {
      serviceId.value = list[0].id;
    }
  });

  async function save() {
    if (!canEdit() || !serviceId.value) return;
    loading.value = true;
    error.value = '';
    try {
      await quickDeparture(serviceId.value, departure.value);
      onSaved(adminStr(cfg, 'mobileDepartureSaved'));
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'mobileSaveFailed');
    } finally {
      loading.value = false;
    }
  }

  return {
    cfg,
    serviceId,
    departure,
    firstStopName,
    loading,
    error,
    save,
  };
}
