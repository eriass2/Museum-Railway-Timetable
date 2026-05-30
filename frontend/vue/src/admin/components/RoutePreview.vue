<script setup lang="ts">
import { computed } from 'vue';
import {
  buildRoutePreviewNodes,
  routePreviewTypeLabel,
  type RoutePreviewNode,
} from '../utils/routePreviewNodes';

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
    label: 'Ruttens stationer',
  },
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
  if (role === 'start') return 'Start';
  if (role === 'end') return 'Slut';
  if (role === 'both') return 'Start/slut';
  return '';
}
</script>

<template>
  <div
    v-if="nodes.length"
    class="mrt-route-preview"
    :class="{ 'mrt-route-preview--compact': compact }"
    role="list"
    :aria-label="label"
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
          {{ routePreviewTypeLabel(node.station_type) }}
        </span>
      </span>
    </template>
  </div>
  <p v-else class="description mrt-route-preview__empty">Inga stationer på rutten.</p>
</template>
