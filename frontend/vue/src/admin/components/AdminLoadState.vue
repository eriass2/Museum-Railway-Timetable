<script setup lang="ts">
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
    <p>
      <button type="button" class="button" @click="$emit('retry')">Försök igen</button>
    </p>
  </div>
  <slot v-else />
</template>
