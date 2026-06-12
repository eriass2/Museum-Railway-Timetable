<script setup lang="ts">
defineProps<{
  label?: string;
  labelFor?: string;
  description?: string;
}>();
</script>

<template>
  <p class="mrt-admin-trip-fields__field">
    <label v-if="label" :for="labelFor">{{ label }}</label>
    <slot />
    <span v-if="description" class="description">{{ description }}</span>
  </p>
</template>

<style scoped>
.mrt-admin-trip-fields__field {
  margin: 0;
}

.mrt-admin-trip-fields__field label {
  display: block;
  font-weight: 600;
  margin-bottom: 4px;
}

.mrt-admin-trip-fields__field :deep(.description) {
  display: block;
  margin-top: 4px;
}
</style>
