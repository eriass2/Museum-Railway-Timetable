<script setup lang="ts">
import { computed } from 'vue';
import MrtAlert from '@/components/ui/MrtAlert.vue';
import type { MrtAlertVariant } from '@/components/ui/types';

const props = withDefaults(
  defineProps<{
    message?: string;
    type?: 'success' | 'error' | 'info' | 'warning';
  }>(),
  { type: 'success' },
);

const variant = computed((): MrtAlertVariant => props.type);
</script>

<template>
  <MrtAlert v-if="message" context="admin" :variant="variant">
    {{ message }}
  </MrtAlert>
</template>
