<script setup lang="ts">
import { RouterView } from 'vue-router';
import AdminConfirmDialog from './components/AdminConfirmDialog.vue';
</script>

<template>
  <div class="mrt-admin-app">
    <RouterView />
    <AdminConfirmDialog />
  </div>
</template>
