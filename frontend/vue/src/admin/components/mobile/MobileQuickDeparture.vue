<script setup lang="ts">
import { adminFmtN, adminStr } from '../../utils/adminLabels';
import { useMobileQuickDeparture } from '../../composables/mobile/useMobileQuickDeparture';
import type { TimetableServiceRow } from '../../types';
import { MrtAlert, MrtButton } from '../ui';

const props = defineProps<{
  services: TimetableServiceRow[];
  canEdit: boolean;
}>();

const emit = defineEmits<{ saved: [message: string] }>();

const {
  cfg,
  serviceId,
  departure,
  firstStopName,
  loading,
  error,
  save,
} = useMobileQuickDeparture(
  () => props.services,
  () => props.canEdit,
  (message) => emit('saved', message),
);
</script>

<template>
  <div class="mrt-admin-mobile-departure">
    <h3>{{ adminStr(cfg, 'mobileQuickDepartureTitle') }}</h3>
    <p class="description">{{ adminStr(cfg, 'mobileQuickDepartureHint') }}</p>
    <p v-if="serviceId && firstStopName" class="description">
      {{ adminFmtN(cfg, 'mobileQuickDepartureWarning', { 1: firstStopName }) }}
    </p>
    <MrtAlert v-if="error" context="admin" variant="error">{{ error }}</MrtAlert>
    <p>
      <label for="mrt-mobile-service">{{ adminStr(cfg, 'mobileTripLabel') }}</label>
      <select id="mrt-mobile-service" v-model.number="serviceId" class="widefat">
        <option :value="0">{{ adminStr(cfg, 'editorSelectTrip') }}</option>
        <option v-for="s in services" :key="s.id" :value="s.id">
          {{ s.service_number }} — {{ s.destination || s.route_name }}
        </option>
      </select>
    </p>
    <p v-if="serviceId">
      <label for="mrt-mobile-departure">
        {{ firstStopName }} — {{ adminStr(cfg, 'mobileDepartureSuffix') }}
      </label>
      <input
        id="mrt-mobile-departure"
        v-model="departure"
        type="time"
        class="widefat"
        :disabled="!canEdit || loading"
      />
    </p>
    <p v-if="canEdit">
      <MrtButton
        context="admin"
        variant="primary"
        wide
        :disabled="!serviceId || loading"
        @click="save"
      >
        {{ adminStr(cfg, 'mobileSaveDeparture') }}
      </MrtButton>
    </p>
  </div>
</template>

<style scoped>
.mrt-admin-mobile-departure {
  margin-top: 16px;
  padding-top: 12px;
  border-top: 1px solid #dcdcde;
}
</style>
