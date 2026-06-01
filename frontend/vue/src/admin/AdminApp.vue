<script setup lang="ts">
import { RouterView } from 'vue-router';
import AdminConfirmDialog from './components/AdminConfirmDialog.vue';
import AdminNav from './components/AdminNav.vue';
</script>

<template>
  <div class="mrt-admin-app mrt-admin-shell">
    <AdminNav />
    <div class="mrt-admin-shell__main">
      <RouterView />
    </div>
    <AdminConfirmDialog />
  </div>
</template>
