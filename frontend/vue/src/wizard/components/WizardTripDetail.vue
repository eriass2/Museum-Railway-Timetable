<script setup lang="ts">
import type { MaybeRef } from 'vue';
import type { WizardVueConfig } from '../../config/types';
import type { JourneyConnection } from '../types';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStr } from '../utils/wizardLabels';
import { useConnectionDetail } from '../composables/useConnectionDetail';
import WizardDetailSegment from './WizardDetailSegment.vue';

const props = defineProps<{
  config: WizardVueConfig;
  cfg: MaybeRef<WizardCfg>;
  connection: JourneyConnection;
  legFrom: MaybeRef<number>;
  legTo: MaybeRef<number>;
}>();

const {
  cfg,
  loading,
  error,
  segments,
  loaded,
  isMulti,
  transferLabel,
  ensureLoaded,
} = useConnectionDetail(props);

defineExpose({ ensureLoaded });
</script>

<template>
  <div
    class="mrt-journey-wizard__detail"
    :class="{ 'mrt-journey-wizard__detail--multi': isMulti }"
  >
    <p v-if="loading" class="mrt-empty">{{ cfgStr(cfg, 'loading', 'Laddar...') }}</p>
    <p v-else-if="error" class="mrt-alert mrt-alert-error">{{ error }}</p>
    <template v-else-if="loaded">
      <WizardDetailSegment
        v-for="(seg, si) in segments"
        :key="si"
        :cfg="cfg"
        :segment="seg"
        :show-transfer="isMulti && si < segments.length - 1"
        :transfer-text="transferLabel()"
      />
    </template>
  </div>
</template>
