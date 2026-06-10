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
  margin-top: 16px;
  padding: 12px 16px;
  background: #fff;
  border: 1px solid #c3c4c7;
  box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
}

.admin-panel__title {
  margin: 0 0 12px;
  font-size: 1.05em;
  line-height: 1.3;
}
</style>
