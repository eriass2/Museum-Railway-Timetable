<script setup lang="ts">
import { computed } from 'vue';
import { trainTypeIconUrl } from '../../shared/trainTypeIcons';
import { adminConfig } from '../types';

const props = withDefaults(
  defineProps<{
    iconKey?: string;
    label?: string;
    size?: number;
  }>(),
  {
    iconKey: '',
    label: '',
    size: 24,
  },
);

const cfg = adminConfig();

const src = computed(() => {
  if (!props.iconKey) {
    return '';
  }
  return trainTypeIconUrl(cfg.trainTypeIconUrls, props.iconKey);
});
</script>

<template>
  <img
    v-if="src"
    class="train-type-icon"
    :src="src"
    :alt="label"
    :title="label || undefined"
    :width="size"
    :height="size"
  />
</template>

<style scoped>
.train-type-icon {
  display: block;
  flex-shrink: 0;
}
</style>
