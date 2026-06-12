<script setup lang="ts">
import { computed } from 'vue';
import MrtPublicAppShell from '@/components/layout/MrtPublicAppShell.vue';
import MrtAlert from '@/components/ui/MrtAlert.vue';
import MrtTimetableIndexView from '@/components/timetable-index/MrtTimetableIndexView.vue';
import type { IndexVueConfig } from '@/config/types';
import '../styles/timetable-index.css';

const props = defineProps<{ config: IndexVueConfig }>();

const items = computed(() => props.config.items ?? []);
const labels = computed(() => props.config.labels ?? { intro: '', navAria: '' });
const showIntro = computed(() => props.config.showIntro !== false);
</script>

<template>
  <MrtPublicAppShell>
    <MrtAlert v-if="config.emptyMessage" variant="info">
      {{ config.emptyMessage }}
    </MrtAlert>
    <MrtTimetableIndexView
      v-else
      :items="items"
      :show-intro="showIntro"
      :labels="labels"
    />
  </MrtPublicAppShell>
</template>
