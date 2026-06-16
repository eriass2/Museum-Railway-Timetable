<script setup lang="ts">
defineProps<{
  categoryKey: string;
}>();
</script>

<template>
  <svg
    v-if="categoryKey === 'train'"
    class="mrt-tf-category__icon"
    viewBox="0 0 24 24"
    aria-hidden="true"
    fill="none"
  >
    <rect x="5" y="8" width="14" height="9" rx="1.5" stroke="currentColor" stroke-width="2" />
    <path d="M8 17v2M16 17v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    <path d="M5 12h14" stroke="currentColor" stroke-width="2" />
    <circle cx="8.5" cy="15" r="1" fill="currentColor" />
    <circle cx="15.5" cy="15" r="1" fill="currentColor" />
  </svg>
  <svg
    v-else-if="categoryKey === 'bus'"
    class="mrt-tf-category__icon"
    viewBox="0 0 24 24"
    aria-hidden="true"
    fill="none"
  >
    <rect x="4" y="6" width="16" height="11" rx="2" stroke="currentColor" stroke-width="2" />
    <path d="M4 11h16" stroke="currentColor" stroke-width="2" />
    <circle cx="8" cy="17" r="1.5" fill="currentColor" />
    <circle cx="16" cy="17" r="1.5" fill="currentColor" />
  </svg>
  <svg
    v-else
    class="mrt-tf-category__icon"
    viewBox="0 0 24 24"
    aria-hidden="true"
    fill="none"
  >
    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" />
    <path
      d="M12 10v6M12 8h.01"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
    />
  </svg>
</template>
