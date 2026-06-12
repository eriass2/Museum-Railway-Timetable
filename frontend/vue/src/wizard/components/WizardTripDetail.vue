<script setup lang="ts">
import { computed } from 'vue';
import MrtAsyncState from '../../components/ui/MrtAsyncState.vue';
import MrtDetailPanel from '../../components/ui/MrtDetailPanel.vue';
import { useWizardContext } from '../../composables/useWizardContext';
import type { JourneyConnection } from '../types';
import { cfgStr } from '../utils/wizardLabels';
import { useConnectionDetail } from '../composables/useConnectionDetail';
import WizardDetailSegment from './WizardDetailSegment.vue';
import { tripFootnotesFromStops } from '../../shared/stopTimeFootnotes';

const props = defineProps<{
  connection: JourneyConnection;
  legCtx: 'outbound' | 'return';
}>();

const { store, cfg, config } = useWizardContext();

const legFrom = computed(() => (props.legCtx === 'return' ? store.toId : store.fromId));
const legTo = computed(() => (props.legCtx === 'return' ? store.fromId : store.toId));
const dateYmd = computed(() => store.dateYmd);

const {
  loading,
  error,
  segments,
  loaded,
  isMulti,
  transferLabelAt,
  ensureLoaded,
} = useConnectionDetail({
  config,
  cfg,
  connection: props.connection,
  legFrom,
  legTo,
  dateYmd,
});

const tripFootnotes = computed(() =>
  tripFootnotesFromStops(
    segments.value.flatMap((seg) => seg.stops),
    cfg.value,
  ),
);

defineExpose({ ensureLoaded });
</script>

<template>
  <MrtDetailPanel :class="{ 'mrt-detail-panel--multi': isMulti }">
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
          :transfer-text="transferLabelAt(si)"
        />
        <dl v-if="tripFootnotes.length" class="mrt-detail-footnotes">
          <div
            v-for="entry in tripFootnotes"
            :key="entry.mark"
            class="mrt-detail-footnotes__row"
          >
            <dt class="mrt-detail-footnotes__mark">{{ entry.mark }}</dt>
            <dd class="mrt-detail-footnotes__text">{{ entry.text }}</dd>
          </div>
        </dl>
      </template>
    </MrtAsyncState>
  </MrtDetailPanel>
</template>

<style scoped>
.mrt-detail-footnotes {
  margin: 1rem 0 0;
  padding: 0.75rem 0 0;
  border-top: 1px solid var(--mrt-wizard-border, #ddd);
  color: #333333;
  font-size: 0.95rem;
  font-weight: 600;
  line-height: 1.45;
}

.mrt-detail-footnotes__row {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  gap: 0.35rem 0.5rem;
  margin: 0;
}

.mrt-detail-footnotes__row + .mrt-detail-footnotes__row {
  margin-top: 0.35rem;
}

.mrt-detail-footnotes__mark {
  margin: 0;
  font-weight: 700;
}

.mrt-detail-footnotes__text {
  margin: 0;
  font-weight: 600;
}
</style>
