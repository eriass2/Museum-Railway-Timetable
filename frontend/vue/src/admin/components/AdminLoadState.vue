<script setup lang="ts">
import MrtAsyncState from '@/components/ui/MrtAsyncState.vue';
import { adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

defineProps<{
  loading?: boolean;
  error?: string;
  loadingText?: string;
}>();

defineEmits<{ retry: [] }>();

const cfg = adminConfig();
</script>

<template>
  <MrtAsyncState
    context="admin"
    :loading="loading"
    :error="error"
    :loading-text="loadingText || adminStr(cfg, 'loading', 'Laddar…')"
    :retry-label="adminStr(cfg, 'retry', 'Försök igen')"
    @retry="$emit('retry')"
  >
    <slot />
  </MrtAsyncState>
</template>
