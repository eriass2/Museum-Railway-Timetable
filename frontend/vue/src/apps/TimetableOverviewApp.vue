<script setup lang="ts">
import { computed, onMounted } from 'vue';
import MrtPublicAppShell from '../components/layout/MrtPublicAppShell.vue';
import MrtStack from '../components/ui/MrtStack.vue';
import { overviewUiLabels } from '../shared/overviewUiLabels';
import MrtHtmlPanel from '../components/ui/MrtHtmlPanel.vue';
import MrtTimetableOverviewView from '../components/overview/MrtTimetableOverviewView.vue';
import type { OverviewVueConfig } from '../config/types';
import { useTimetableOverview } from '../composables/useTimetableOverview';
import { resolveMrtString } from '../utils/mrtStrings';

const props = defineProps<{ config: OverviewVueConfig }>();

const embedded = computed(() => Boolean(props.config.embedded));
const hasInlineData = computed(() => Boolean(props.config.overview));

const { overview, loading, error, fetchOverview } = useTimetableOverview(props.config);
const overviewLabels = computed(() => overviewUiLabels(props.config));

onMounted(() => {
  if (props.config.overview) {
    overview.value = props.config.overview;
    return;
  }
  void fetchOverview(props.config.timetableId);
});
</script>

<template>
  <MrtPublicAppShell :constrain-content="!embedded" :content-padding="!embedded">
  <MrtStack
    as="div"
    class="mrt-vue-overview"
    :class="{ 'mrt-vue-overview--embedded': embedded }"
    :margin-top="embedded ? 'none' : 'lg'"
    :margin-bottom="embedded ? 'none' : 'lg'"
  >
    <MrtHtmlPanel
      :visible="true"
      surface
      box
      :loading="!hasInlineData && loading"
      :error="error"
      :loading-text="resolveMrtString(config, 'loading', 'Laddar...')"
    >
      <MrtTimetableOverviewView v-if="overview" :data="overview" :labels="overviewLabels" />
    </MrtHtmlPanel>
  </MrtStack>
  </MrtPublicAppShell>
</template>

<style scoped>
.mrt-vue-overview {
  min-width: 0;
  max-width: 100%;
}
</style>
