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
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .form-table th),
  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .form-table td) {
    display: block;
    width: 100%;
    padding: 4px 0;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .form-table input[type='text']),
  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .form-table input[type='number']) {
    width: 100%;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .form-table .button-primary) {
    width: 100%;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-admin-import-export-options) {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-admin-import-export-options .mrt-ml-sm) {
    margin-left: 0;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-price-matrix-table input.small-text) {
    min-width: 3.5em;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .train-types-page__field--slug .regular-text) {
    max-width: none;
    width: 100%;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-admin-responsive-table thead) {
    display: none;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-admin-responsive-table tbody tr) {
    display: block;
    margin-bottom: 10px;
    padding: 10px 12px;
    background: #fff;
    border: 1px solid #c3c4c7;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-admin-responsive-table tbody td) {
    display: block;
    padding: 4px 0;
    border: 0;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-admin-responsive-table tbody td[data-label]::before) {
    content: attr(data-label);
    display: block;
    margin-bottom: 2px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: #50575e;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-admin-responsive-table tbody td:last-child) {
    margin-top: 6px;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-admin-responsive-table tbody td .regular-text),
  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-admin-responsive-table tbody td .small-text) {
    width: 100%;
    max-width: none;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-admin-responsive-table tbody td .button) {
    width: 100%;
    justify-content: center;
    text-align: center;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-admin-card-list) {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-admin-card-list__item) {
    margin-bottom: 10px;
    padding: 12px 14px;
    background: #fff;
    border: 1px solid #c3c4c7;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-admin-card-list__item .admin-row-actions) {
    margin-top: 8px;
  }

  :deep(.mrt-admin-shell__main .mrt-admin-page--mobile .mrt-admin-card-list__empty) {
    padding: 12px 0;
    color: #646970;
  }
}
</style>
