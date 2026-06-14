<script setup lang="ts">
defineProps<{
  variant: 'info' | 'warning';
  count: number;
}>();
</script>

<template>
  <span
    class="mrt-tf-count-badge"
    :class="`mrt-tf-count-badge--${variant}`"
  >
    <span class="mrt-tf-count-badge__mark" aria-hidden="true">
      {{ variant === 'info' ? 'i' : '!' }}
    </span>
    <span class="mrt-tf-count-badge__count">{{ count }}</span>
  </span>
</template>
