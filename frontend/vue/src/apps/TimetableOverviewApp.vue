<script setup lang="ts">
import { onMounted } from 'vue';
import MrtHtmlPanel from '../components/ui/MrtHtmlPanel.vue';
import MrtTimetableOverviewView from '../components/overview/MrtTimetableOverviewView.vue';
import type { OverviewVueConfig } from '../config/types';
import { useTimetableOverview } from '../composables/useTimetableOverview';
import { resolveMrtString } from '../utils/mrtStrings';

const props = defineProps<{ config: OverviewVueConfig }>();

const { overview, loading, error, fetchOverview } = useTimetableOverview(props.config);

onMounted(() => {
  void fetchOverview(props.config.timetableId);
});
</script>

<template>
  <div class="mrt-vue-overview mrt-my-lg">
    <MrtHtmlPanel
      :visible="true"
      surface
      box
      :loading="loading"
      :error="error"
      :loading-text="resolveMrtString(config, 'loading', 'Laddar...')"
    >
      <MrtTimetableOverviewView v-if="overview" :data="overview" />
    </MrtHtmlPanel>
  </div>
</template>
