<script setup lang="ts">
import { computed } from 'vue';
import {
  buildRoutePreviewNodes,
  routePreviewRoleLabel,
  routePreviewTypeLabel,
} from '../../utils/stations-routes/routePreviewNodes';
import { adminConfig } from '../../types';
import { adminStr } from '../../utils/adminLabels';

const props = withDefaults(
  defineProps<{
    stationIds: number[];
    stationsById: Map<number, { title: string; station_type?: string }>;
    startStationId?: number;
    endStationId?: number;
    compact?: boolean;
    label?: string;
  }>(),
  {
    startStationId: 0,
    endStationId: 0,
    compact: false,
    label: '',
  },
);

const cfg = adminConfig();
const labelFor = (key: string) => adminStr(cfg, key);

const displayLabel = computed(
  () => props.label || adminStr(cfg, 'routePreviewLabel'),
);

const nodes = computed(() =>
  buildRoutePreviewNodes(
    props.stationIds,
    props.stationsById,
    props.startStationId,
    props.endStationId,
  ),
);
</script>

<template>
  <div
    v-if="nodes.length"
    class="mrt-route-preview"
    :class="{ 'mrt-route-preview--compact': compact }"
    role="list"
    :aria-label="displayLabel"
  >
    <template v-for="(node, index) in nodes" :key="node.id">
      <span v-if="index > 0" class="mrt-route-preview__arrow" aria-hidden="true">→</span>
      <span
        class="mrt-route-preview__node"
        :class="`mrt-route-preview__node--${node.role}`"
        role="listitem"
      >
        <span class="mrt-route-preview__name">{{ node.name }}</span>
        <span v-if="node.role !== 'via'" class="mrt-route-preview__role">
          {{ routePreviewRoleLabel(node.role, labelFor) }}
        </span>
        <span v-if="node.station_type" class="mrt-route-preview__type">
          {{ routePreviewTypeLabel(node.station_type, labelFor) }}
        </span>
      </span>
    </template>
  </div>
  <p v-else class="description mrt-route-preview__empty">
    {{ adminStr(cfg, 'routePreviewEmpty') }}
  </p>
</template>
