import { computed, ref, watch, type Ref } from 'vue';
import type { PricesPayload } from '../../api/adminRest';
import type { AdminClientConfig } from '../../types';
import { adminStr } from '../../utils/adminLabels';
import {
  adminAfternoonCompareLabels,
  adminAfternoonVisitorNote,
  adminPricePreviewCfg,
  buildAdminPricePreviewTrip,
  effectivePricingZones,
} from '../../utils/prices/adminPricePreview';

export function useAfternoonPriceCompare(
  payload: Ref<PricesPayload>,
  thresholdMinutes: Ref<number>,
  cfg: AdminClientConfig,
) {
  const compareZone = ref(2);
  const pricingZones = computed(() => effectivePricingZones(payload.value));

  watch(
    pricingZones,
    (zones) => {
      if (!zones.includes(compareZone.value)) {
        compareZone.value = zones[1] ?? zones[0] ?? 1;
      }
    },
    { immediate: true },
  );

  const priceCfg = computed(() => ({
    ...adminPricePreviewCfg(cfg),
    priceAfternoonNote: adminAfternoonVisitorNote(cfg, thresholdMinutes.value),
  }));

  const normalReturn = computed(() =>
    buildAdminPricePreviewTrip(payload.value, 'return', compareZone.value, false),
  );

  const afternoonReturn = computed(() =>
    buildAdminPricePreviewTrip(payload.value, 'return', compareZone.value, true),
  );

  const compareLabels = computed(() => adminAfternoonCompareLabels(cfg, payload.value));

  const normalCompareLabels = computed(() => ({
    ...compareLabels.value,
    titleSuffix: `(${adminStr(cfg, 'pricesZoneLabel', 'Zon')} ${compareZone.value})`,
  }));

  return {
    compareZone,
    pricingZones,
    priceCfg,
    normalReturn,
    afternoonReturn,
    compareLabels,
    normalCompareLabels,
  };
}
