<script setup lang="ts">
import {
  mobileCancelResultLabels,
  useCancelTrafficToday,
} from '../../composables/useCancelTrafficToday';
import type { TrafficToday } from '../../types';
import { adminConfig } from '../../types';
import { adminFmt, adminFmtN, adminStr } from '../../utils/adminLabels';
import { MrtButton } from '../ui';

const props = defineProps<{
  traffic: TrafficToday;
  canOperate: boolean;
}>();

const emit = defineEmits<{ done: [message: string]; error: [message: string] }>();

const cfg = adminConfig();
const resultLabels = mobileCancelResultLabels(cfg);

const { busy, cancelAll: runCancelAll } = useCancelTrafficToday(
  cfg,
  () => props.traffic,
  () => props.canOperate,
  () => ({
    title: adminStr(cfg, 'mobileCancelConfirmTitle'),
    message: adminFmtN(cfg, 'mobileCancelConfirmMessage', {
      1: props.traffic.services_count,
      2: props.traffic.date,
    }),
    confirmLabel: adminStr(cfg, 'trafficTodayCancelButton'),
  }),
  () => resultLabels,
);

async function cancelAll() {
  try {
    const result = await runCancelAll();
    if (result !== null) {
      emit('done', result);
    }
  } catch (e) {
    emit('error', e instanceof Error ? e.message : adminStr(cfg, 'trafficTodayCancelFailed'));
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
