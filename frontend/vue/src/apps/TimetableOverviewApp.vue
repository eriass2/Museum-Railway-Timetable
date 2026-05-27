<script setup lang="ts">
import { onMounted, ref } from 'vue';
import type { OverviewVueConfig } from '../config/types';
import { useMrtAjax } from '../composables/useMrtAjax';
import { msg } from '../api/mrtApi';

const props = defineProps<{ config: OverviewVueConfig }>();

const html = ref('');
const { loading, error, run } = useMrtAjax(props.config);

onMounted(async () => {
  const id = props.config.timetableId;
  if (!id) {
    error.value = msg(props.config, 'errorLoading', 'Tidtabell hittades inte.');
    return;
  }

  const res = await run<{ html: string }>('mrt_timetable_overview_html', {
    timetable_id: id,
  });

  if (res.success && res.data?.html) {
    html.value = res.data.html;
  }
});
</script>

<template>
  <div class="mrt-vue-overview">
    <p v-if="loading" class="mrt-empty mrt-empty--loading">
      {{ msg(config, 'loading', 'Laddar...') }}
    </p>
    <div v-else-if="error" class="mrt-alert mrt-alert-error" role="alert">{{ error }}</div>
    <div v-else v-html="html" />
  </div>
</template>
