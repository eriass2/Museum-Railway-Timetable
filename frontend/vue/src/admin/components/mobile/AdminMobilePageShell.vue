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
@import '../../styles/mobile/responsive-forms.css';
@import '../../styles/mobile/responsive-tables.css';
@import '../../styles/mobile/responsive-cards.css';
</style>
