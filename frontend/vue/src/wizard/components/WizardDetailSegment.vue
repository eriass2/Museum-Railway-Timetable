<script setup lang="ts">
import { unref, type MaybeRef } from 'vue';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import type { LegSegment } from '../composables/useConnectionDetail';
import { cfgStr } from '../utils/wizardLabels';
import { formatDuration } from '../utils/format';
import { legVehicleKind, legVehicleLabel, trainIconUrl } from '../utils/vehicle';
import WizardTimeline from './WizardTimeline.vue';

defineProps<{
  cfg: MaybeRef<WizardCfg>;
  segment: LegSegment;
  showTransfer: boolean;
  transferText: string;
}>();
</script>

<template>
  <div class="mrt-journey-wizard__detail-segment mrt-mb-sm">
    <h4 v-if="segment.title" class="mrt-journey-wizard__detail-title">{{ segment.title }}</h4>
    <p v-if="segment.notice" class="mrt-journey-wizard__notice">
      <strong>{{ cfgStr(cfg, 'noticeLabel', 'Notis') }}:</strong> {{ segment.notice }}
    </p>
    <div v-if="segment.leg" class="mrt-journey-wizard__timeline-leg">
      <span v-if="segment.leg.duration_minutes" class="mrt-journey-wizard__leg-duration">
        {{ formatDuration(segment.leg.duration_minutes, unref(cfg)) }}
      </span>
      <span
        class="mrt-journey-wizard__vehicle"
        :class="`mrt-journey-wizard__vehicle--${legVehicleKind(segment.leg, unref(cfg))}`"
      >
        <img
          v-if="trainIconUrl(legVehicleKind(segment.leg, unref(cfg)), unref(cfg))"
          :src="trainIconUrl(legVehicleKind(segment.leg, unref(cfg)), unref(cfg))"
          class="mrt-journey-wizard__vehicle-icon"
          width="48"
          height="24"
          alt=""
        >
        <span>{{ legVehicleLabel(segment.leg) }}</span>
      </span>
    </div>
    <WizardTimeline :cfg="unref(cfg)" :stops="segment.stops" :start-expanded="false" />
    <div v-if="showTransfer" class="mrt-journey-wizard__transfer-block">
      {{ transferText }}
    </div>
  </div>
</template>
