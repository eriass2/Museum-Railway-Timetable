<script setup lang="ts">
import { onMounted, ref } from 'vue';
import type { MrtVueConfig } from '../useMrtConfig';
import { mrtPost, msg } from '../api/mrtApi';

const props = defineProps<{ config: MrtVueConfig }>();

const html = ref('');
const loading = ref(true);
const error = ref('');

onMounted(async () => {
  const id = Number(props.config.timetableId);
  if (!id) {
    error.value = msg(props.config, 'errorLoading', 'Timetable not found.');
    loading.value = false;
    return;
  }

  const res = await mrtPost<{ html: string }>(props.config, 'mrt_timetable_overview_html', {
    timetable_id: id,
  });

  loading.value = false;
  if (!res.success || !res.data?.html) {
    error.value = res.message || msg(props.config, 'errorLoading', 'Error loading timetable.');
    return;
  }
  html.value = res.data.html;
});
</script>

<template>
  <div class="mrt-vue-overview">
    <p v-if="loading" class="mrt-empty mrt-empty--loading">
      {{ msg(config, 'loading', 'Loading...') }}
    </p>
    <div v-else-if="error" class="mrt-alert mrt-alert-error" role="alert">{{ error }}</div>
    <div v-else v-html="html" />
  </div>
</template>
