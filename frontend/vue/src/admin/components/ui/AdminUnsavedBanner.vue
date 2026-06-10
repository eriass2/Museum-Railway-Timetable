<script setup lang="ts">
defineProps<{
  show: boolean;
  message: string;
}>();
</script>

<template>
  <p v-if="show" class="notice notice-warning mrt-admin-unsaved">
    {{ message }}
  </p>
</template>
