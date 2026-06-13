<script setup lang="ts">
defineOptions({ inheritAttrs: false });

defineProps<{
  mobile?: boolean;
}>();
</script>

<template>
  <div
    class="mrt-admin-page"
    :class="{ 'mrt-admin-page--mobile': mobile }"
    v-bind="$attrs"
  >
    <slot />
  </div>
</template>

<style scoped>
@media (max-width: 782px) {
  .mrt-admin-page--mobile :deep(.form-table th),
  .mrt-admin-page--mobile :deep(.form-table td) {
    display: block;
    width: 100%;
    padding: 4px 0;
  }

  .mrt-admin-page--mobile :deep(.form-table input[type='text']),
  .mrt-admin-page--mobile :deep(.form-table input[type='number']) {
    width: 100%;
  }

  .mrt-admin-page--mobile :deep(.form-table .button-primary) {
    width: 100%;
  }

  .mrt-admin-page--mobile :deep(.mrt-admin-import-export-options) {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .mrt-admin-page--mobile :deep(.mrt-admin-import-export-options .mrt-ml-sm) {
    margin-left: 0;
  }

  .mrt-admin-page--mobile :deep(.mrt-price-matrix-table input.small-text) {
    min-width: 3.5em;
  }

  .mrt-admin-page--mobile :deep(.train-types-page__field--slug .regular-text) {
    max-width: none;
    width: 100%;
  }

  .mrt-admin-page--mobile :deep(.mrt-admin-responsive-table thead) {
    display: none;
  }

  .mrt-admin-page--mobile :deep(.mrt-admin-responsive-table tbody tr) {
    display: block;
    margin-bottom: 10px;
    padding: 10px 12px;
    background: #fff;
    border: 1px solid #c3c4c7;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
  }

  .mrt-admin-page--mobile :deep(.mrt-admin-responsive-table tbody td) {
    display: block;
    padding: 4px 0;
    border: 0;
  }

  .mrt-admin-page--mobile :deep(.mrt-admin-responsive-table tbody td[data-label]::before) {
    content: attr(data-label);
    display: block;
    margin-bottom: 2px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: #50575e;
  }

  .mrt-admin-page--mobile :deep(.mrt-admin-responsive-table tbody td:last-child) {
    margin-top: 6px;
  }

  .mrt-admin-page--mobile :deep(.mrt-admin-responsive-table tbody td .regular-text),
  .mrt-admin-page--mobile :deep(.mrt-admin-responsive-table tbody td .small-text) {
    width: 100%;
    max-width: none;
  }

  .mrt-admin-page--mobile :deep(.mrt-admin-responsive-table tbody td .button) {
    width: 100%;
    justify-content: center;
    text-align: center;
  }

  .mrt-admin-page--mobile :deep(.mrt-admin-card-list) {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .mrt-admin-page--mobile :deep(.mrt-admin-card-list__item) {
    margin-bottom: 10px;
    padding: 12px 14px;
    background: #fff;
    border: 1px solid #c3c4c7;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
  }

  .mrt-admin-page--mobile :deep(.mrt-admin-card-list__item .admin-row-actions) {
    margin-top: 8px;
  }

  .mrt-admin-page--mobile :deep(.mrt-admin-card-list__empty) {
    padding: 12px 0;
    color: #646970;
  }
}
</style>
