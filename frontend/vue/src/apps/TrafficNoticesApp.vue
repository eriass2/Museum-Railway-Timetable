<script setup lang="ts">
import MrtPublicAppShell from '@/components/layout/MrtPublicAppShell.vue';
import MrtTrafficNoticesView from '@/components/traffic-notices/MrtTrafficNoticesView.vue';
import type { TrafficNoticesVueConfig } from '@/types/trafficNotices';

defineProps<{ config: TrafficNoticesVueConfig }>();
</script>

<template>
  <MrtPublicAppShell>
    <MrtTrafficNoticesView :config="config" />
  </MrtPublicAppShell>
</template>
