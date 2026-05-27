<script setup lang="ts">
import { onMounted } from 'vue';
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
    <p v-if="loading" class="mrt-empty mrt-empty--loading">
      {{ resolveMrtString(config, 'loading', 'Laddar...') }}
    </p>
    <div v-else-if="error" class="mrt-alert mrt-alert-error" role="alert">{{ error }}</div>
    <!-- Trusted server HTML — see frontend/vue/TRUSTED_HTML.md -->
    <div v-else v-html="html" />
  </div>
</template>
