<script setup lang="ts">
import { onMounted } from 'vue';
import MrtAsyncState from '../components/ui/MrtAsyncState.vue';
import type { OverviewVueConfig } from '../config/types';
import { useTimetableHtml } from '../composables/useTimetableHtml';
import { resolveMrtString } from '../utils/mrtStrings';

const props = defineProps<{ config: OverviewVueConfig }>();

const { html, loading, error, fetchOverviewHtml } = useTimetableHtml(props.config);

onMounted(() => {
  void fetchOverviewHtml(props.config.timetableId);
});
</script>

<template>
  <div class="mrt-vue-overview">
    <MrtAsyncState
      :loading="loading"
      :error="error"
      :loading-text="resolveMrtString(config, 'loading', 'Laddar...')"
    >
      <!-- Trusted server HTML — see frontend/vue/TRUSTED_HTML.md -->
      <div v-html="html" />
    </MrtAsyncState>
  </div>
</template>
