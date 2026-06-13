<script setup lang="ts">
defineOptions({ inheritAttrs: false });

defineProps<{
  title?: string;
}>();
</script>

<template>
  <section class="admin-panel" v-bind="$attrs">
    <h2 v-if="title" class="admin-panel__title">{{ title }}</h2>
    <slot />
  </section>
</template>

<style scoped>
.admin-panel {
  margin-top: var(--mrt-admin-panel-margin-top, 16px);
  padding: var(--mrt-admin-panel-padding-block, 12px) var(--mrt-admin-panel-padding-inline, 16px);
  background: var(--mrt-admin-panel-bg, #fff);
  border: 1px solid var(--mrt-admin-panel-border, #c3c4c7);
  box-shadow: var(--mrt-admin-panel-shadow, 0 1px 1px rgba(0, 0, 0, 0.04));
}

.admin-panel__title {
  margin: 0 0 var(--mrt-admin-panel-title-margin-bottom, 12px);
  font-size: 1.05em;
  line-height: 1.3;
}
</style>
