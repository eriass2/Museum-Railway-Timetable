<script setup lang="ts">
import { ref } from 'vue';
import { cancelTrafficToday } from '../../api/adminRest';
import { adminConfirm } from '../../composables/adminConfirm';
import type { TrafficToday } from '../../types';
import { adminConfig } from '../../types';
import { adminErrorMessage, adminFmt, adminFmtN, adminStr } from '../../utils/adminLabels';
import { MrtButton } from '../ui';

const props = defineProps<{
  traffic: TrafficToday;
  canOperate: boolean;
}>();

const emit = defineEmits<{ done: [message: string]; error: [message: string] }>();

const cfg = adminConfig();
const busy = ref(false);

async function cancelAll() {
  if (!props.canOperate || busy.value) return;
  const label = props.traffic.date;
  const ok = await adminConfirm({
    title: adminStr(cfg, 'mobileCancelConfirmTitle'),
    message: adminFmtN(cfg, 'mobileCancelConfirmMessage', {
      1: props.traffic.services_count,
      2: label,
    }),
    confirmLabel: adminStr(cfg, 'trafficTodayCancelButton'),
    danger: true,
  });
  if (!ok) {
    return;
  }
  busy.value = true;
  try {
    const notice = adminStr(cfg, 'trafficCancelledNotice');
    const res = await cancelTrafficToday(props.traffic.date, notice);
    emit(
      'done',
      res.services_updated > 0
        ? adminFmt(cfg, 'mobileCancelSuccess', res.services_updated)
        : adminStr(cfg, 'mobileCancelNone'),
    );
  } catch (e) {
    emit('error', adminErrorMessage(cfg, e, 'trafficTodayCancelFailed'));
  } finally {
    busy.value = false;
  }
}
</script>

<template>
  <div class="mrt-admin-mobile-cancel">
    <h3>{{ adminStr(cfg, 'mobileCancelTitle') }}</h3>
    <p class="description">
      {{ adminFmt(cfg, 'mobileCancelHint', traffic.date) }}
    </p>
    <p v-if="traffic.all_cancelled" class="notice notice-info">
      {{ adminStr(cfg, 'mobileCancelAllCancelled') }}
    </p>
    <p v-else-if="canOperate">
      <MrtButton
        context="admin"
        variant="primary"
        wide
        :disabled="busy || traffic.services_count === 0"
        @click="cancelAll"
      >
        {{ adminStr(cfg, 'mobileCancelButton') }}
      </MrtButton>
    </p>
    <p v-else class="description">{{ adminStr(cfg, 'mobileCancelNoPermission') }}</p>
  </div>
</template>
