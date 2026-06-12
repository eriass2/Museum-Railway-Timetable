<script setup lang="ts">
import { AdminDisclosure } from '../ui';
import RouteStationOrderEditor from './RouteStationOrderEditor.vue';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import type { RouteRow, StationRow } from '../../types';

defineProps<{
  stations: StationRow[];
  stationTitle: (stationId: number) => string;
  idPrefix: string;
  titleInputId: string;
  showCreateHint?: boolean;
}>();

const route = defineModel<RouteRow>({ required: true });

defineEmits<{
  move: [idx: number, dir: -1 | 1];
  remove: [idx: number];
}>();

const cfg = adminConfig();
</script>

<template>
  <div class="mrt-admin-route-editor__section">
    <label class="mrt-admin-route-editor__label" :for="titleInputId">
      {{ adminStr(cfg, 'stationsRouteNameLabel') }}
    </label>
    <input
      :id="titleInputId"
      v-model="route.title"
      type="text"
      class="regular-text"
      :placeholder="adminStr(cfg, 'stationsNewRoute')"
    />
  </div>
  <AdminDisclosure v-if="showCreateHint" :summary="adminStr(cfg, 'stationsRouteCreateMoreFields')">
    <p class="description">{{ adminStr(cfg, 'stationsRouteOrderHint') }}</p>
  </AdminDisclosure>
  <RouteStationOrderEditor
    v-model="route"
    :stations="stations"
    :station-title="stationTitle"
    :id-prefix="idPrefix"
    @move="(idx, dir) => $emit('move', idx, dir)"
    @remove="(idx) => $emit('remove', idx)"
  />
</template>

<style scoped>
.mrt-admin-route-editor__section {
  margin-bottom: 16px;
}

.mrt-admin-route-editor__section:last-child {
  margin-bottom: 0;
}

.mrt-admin-route-editor__label {
  display: block;
  margin-bottom: 4px;
  font-weight: 600;
}

@media (max-width: 782px) {
  :deep(select),
  :deep(.admin-form-actions .button),
  :deep(.admin-inline-form .button) {
    width: 100%;
  }
}
</style>
