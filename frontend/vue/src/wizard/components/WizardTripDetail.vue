<script setup lang="ts">
import { computed } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import type { JourneyConnection } from '../types';
import { cfgStr } from '../utils/wizardLabels';
import { useConnectionDetail } from '../composables/useConnectionDetail';
import WizardDetailSegment from './WizardDetailSegment.vue';

const props = defineProps<{
  connection: JourneyConnection;
  legCtx: 'outbound' | 'return';
}>();

const { store, cfg, config } = useWizardContext();

const legFrom = computed(() => (props.legCtx === 'return' ? store.toId : store.fromId));
const legTo = computed(() => (props.legCtx === 'return' ? store.fromId : store.toId));

const {
  loading,
  error,
  segments,
  loaded,
  isMulti,
  transferLabel,
  ensureLoaded,
} = useConnectionDetail({
  config,
  cfg,
  connection: props.connection,
  legFrom,
  legTo,
});

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
