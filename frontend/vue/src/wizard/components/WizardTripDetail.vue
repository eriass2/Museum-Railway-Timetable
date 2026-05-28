<script setup lang="ts">
import { computed } from 'vue';
import MrtAsyncState from '../../components/ui/MrtAsyncState.vue';
import MrtDetailPanel from '../../components/ui/MrtDetailPanel.vue';
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
  <MrtDetailPanel :multi="isMulti">
    <MrtAsyncState
      :loading="loading"
      :error="error"
      :loading-text="cfgStr(cfg, 'loading', 'Laddar...')"
    >
      <template v-if="loaded">
        <WizardDetailSegment
          v-for="(seg, si) in segments"
          :key="si"
          :cfg="cfg"
          :segment="seg"
          :show-transfer="isMulti && si < segments.length - 1"
          :transfer-text="transferLabel()"
        />
      </template>
    </MrtAsyncState>
  </MrtDetailPanel>
</template>
