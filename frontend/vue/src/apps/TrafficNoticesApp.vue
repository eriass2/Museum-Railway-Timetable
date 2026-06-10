<script setup lang="ts">
import MrtTrafficNoticesView from '@/components/traffic-notices/MrtTrafficNoticesView.vue';
import type { TrafficNoticesVueConfig } from '@/types/trafficNotices';
import '../styles/traffic-notices.css';

defineProps<{ config: TrafficNoticesVueConfig }>();
</script>

<template>
  <MrtTrafficNoticesView :config="config" />
</template>
