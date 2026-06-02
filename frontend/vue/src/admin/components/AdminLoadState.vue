<script setup lang="ts">
import AdminFormActions from './ui/AdminFormActions.vue';
import { adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

defineProps<{
  loading?: boolean;
  error?: string;
  loadingText?: string;
}>();

defineEmits<{
  retry: [];
}>();

const cfg = adminConfig();
</script>

<template>
  <p v-if="loading" class="mrt-empty mrt-empty--loading mrt-admin-async__loading">
    {{ loadingText || adminStr(cfg, 'loading', 'Laddar…') }}
  </p>
  <div v-else-if="error" class="mrt-admin-async__error notice notice-error">
    <p>{{ error }}</p>
    <AdminFormActions>
      <button type="button" class="button" @click="$emit('retry')">
        {{ adminStr(cfg, 'retry', 'Försök igen') }}
      </button>
    </AdminFormActions>
  </div>
  <slot v-else />
</template>
