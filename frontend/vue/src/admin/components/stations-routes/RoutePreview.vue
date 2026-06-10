<script setup lang="ts">
import { computed } from 'vue';
import {
  buildRoutePreviewNodes,
  routePreviewTypeLabel,
  type RoutePreviewNode,
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

const displayLabel = computed(
  () => props.label || adminStr(cfg, 'routePreviewLabel'),
);

const nodes = computed<RoutePreviewNode[]>(() =>
  buildRoutePreviewNodes(
    props.stationIds,
    props.stationsById,
    props.startStationId,
    props.endStationId,
  ),
);

function roleLabel(role: RoutePreviewNode['role']): string {
  if (role === 'start') return adminStr(cfg, 'routePreviewStart');
  if (role === 'end') return adminStr(cfg, 'routePreviewEnd');
  if (role === 'both') return adminStr(cfg, 'routePreviewBoth');
  return '';
}

function stationTypeLabel(stationType: string): string {
  return routePreviewTypeLabel(stationType, (key) => adminStr(cfg, key));
}
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
          {{ roleLabel(node.role) }}
        </span>
        <span v-if="node.station_type" class="mrt-route-preview__type">
          {{ stationTypeLabel(node.station_type) }}
        </span>
      </span>
    </template>
  </div>
  <p v-else class="description mrt-route-preview__empty">
    {{ adminStr(cfg, 'routePreviewEmpty') }}
  </p>
</template>
