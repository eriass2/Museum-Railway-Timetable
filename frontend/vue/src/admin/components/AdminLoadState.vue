<script setup lang="ts">
import AdminFormActions from './ui/AdminFormActions.vue';

defineProps<{
  loading?: boolean;
  error?: string;
  loadingText?: string;
}>();

defineEmits<{
  retry: [];
}>();
</script>

<template>
  <p v-if="loading" class="mrt-empty mrt-empty--loading mrt-admin-async__loading">
    {{ loadingText || 'Laddar...' }}
  </p>
  <div v-else-if="error" class="mrt-admin-async__error notice notice-error">
    <p>{{ error }}</p>
    <AdminFormActions>
      <button type="button" class="button" @click="$emit('retry')">Försök igen</button>
    </AdminFormActions>
  </div>
  <slot v-else />
</template>
