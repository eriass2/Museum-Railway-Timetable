import { ref } from 'vue';
import { cancelTrafficToday } from '../api/adminRest';
import { adminConfirm } from './adminConfirm';
import type { AdminClientConfig, TrafficToday } from '../types';
import { adminErrorMessage, adminFmt, adminStr } from '../utils/adminLabels';

export type CancelTrafficConfirmLabels = {
  title: string;
  message: string;
  confirmLabel: string;
};

export type CancelTrafficResultLabels = {
  success: (servicesUpdated: number) => string;
  none: string;
  errorKey: string;
};

/** Shared confirm + REST flow for cancelling all traffic on a date. */
export function useCancelTrafficToday(
  cfg: AdminClientConfig,
  getTraffic: () => TrafficToday,
  canOperate: () => boolean,
  confirmLabels: () => CancelTrafficConfirmLabels,
  resultLabels: () => CancelTrafficResultLabels,
) {
  const busy = ref(false);

  async function cancelAll(): Promise<string | null> {
    if (!canOperate() || busy.value) {
      return null;
    }
    const traffic = getTraffic();
    const confirm = confirmLabels();
    const ok = await adminConfirm({
      title: confirm.title,
      message: confirm.message,
      confirmLabel: confirm.confirmLabel,
      danger: true,
    });
    if (!ok) {
      return null;
    }
    busy.value = true;
    try {
      const notice = adminStr(cfg, 'trafficCancelledNotice');
      const res = await cancelTrafficToday(traffic.date, notice);
      const labels = resultLabels();
      return res.services_updated > 0
        ? labels.success(res.services_updated)
        : labels.none;
    } catch (e) {
      throw new Error(adminErrorMessage(cfg, e, resultLabels().errorKey));
    } finally {
      busy.value = false;
    }
  }

  return { busy, cancelAll };
}

/** Desktop dashboard success messages for cancel-all. */
export function trafficTodayCancelResultLabels(cfg: AdminClientConfig): CancelTrafficResultLabels {
  return {
    success: (count) => adminFmt(cfg, 'trafficTodayCancelSuccess', count),
    none: adminStr(cfg, 'trafficTodayCancelNone'),
    errorKey: 'trafficTodayCancelFailed',
  };
}

/** Mobile panel success messages for cancel-all. */
export function mobileCancelResultLabels(cfg: AdminClientConfig): CancelTrafficResultLabels {
  return {
    success: (count) => adminFmt(cfg, 'mobileCancelSuccess', count),
    none: adminStr(cfg, 'mobileCancelNone'),
    errorKey: 'trafficTodayCancelFailed',
  };
}
