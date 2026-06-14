<script setup lang="ts">
defineProps<{
  label: string;
}>();
</script>

<template>
  <span class="mrt-tf-line-badge">{{ label }}</span>
</template>
