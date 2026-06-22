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

<style scoped>
.mrt-admin-shell {
  display: flex;
  align-items: flex-start;
  gap: 24px;
  margin-top: 4px;
}

.mrt-admin-shell__main {
  flex: 1;
  min-width: 0;
}

.mrt-admin-shell__main > .mrt-admin-page > h1:first-child,
.mrt-admin-shell__main > div > h1:first-child {
  margin-top: 0;
}

:deep(.widefat td:has(.admin-row-actions)) {
  vertical-align: middle;
}

@media (max-width: 782px) {
  .mrt-admin-shell {
    flex-direction: column;
    gap: 12px;
    max-width: 100%;
    overflow-x: clip;
  }

  .mrt-admin-shell__main {
    width: 100%;
    max-width: 100%;
    overflow-x: clip;
  }
}
</style>
