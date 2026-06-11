import { computed, ref, watch, type Ref } from 'vue';
import type { PricesPayload } from '../../api/adminRest';
import type { AdminClientConfig } from '../../types';
import {
  adminPricePreviewCfg,
  adminPriceTableLabels,
  buildAdminPricePreviewDay,
  buildAdminPricePreviewTrip,
  effectivePricingZones,
} from '../../utils/prices/adminPricePreview';

export function usePricesPreview(payload: Ref<PricesPayload>, cfg: AdminClientConfig) {
  const previewZone = ref(1);
  const previewType = ref('single');

  const ticketKeys = computed(() => Object.keys(payload.value.ticket_types));
  const pricingZones = computed(() => effectivePricingZones(payload.value));

  watch(
    pricingZones,
    (zones) => {
      if (!zones.includes(previewZone.value)) {
        previewZone.value = zones[0] ?? 1;
      }
    },
    { immediate: true },
  );

  watch(ticketKeys, (keys) => {
    if (!keys.includes(previewType.value)) {
      previewType.value = keys[0] ?? 'single';
    }
  });

  const labels = computed(() =>
    adminPriceTableLabels(cfg, payload.value, previewZone.value, true),
  );

  const tripPrice = computed(() =>
    buildAdminPricePreviewTrip(payload.value, previewType.value, previewZone.value, false),
  );

  const dayPrice = computed(() => buildAdminPricePreviewDay(payload.value, previewZone.value));

  const priceCfg = computed(() => adminPricePreviewCfg(cfg));

  return {
    previewZone,
    previewType,
    ticketKeys,
    pricingZones,
    labels,
    tripPrice,
    dayPrice,
    priceCfg,
  };
}
