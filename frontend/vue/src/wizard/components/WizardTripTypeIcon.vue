<script setup lang="ts">
defineProps<{
  variant: 'single' | 'return';
}>();
</script>

<template>
  <span class="mrt-trip-type-icon" aria-hidden="true">
    <svg
      v-if="variant === 'single'"
      viewBox="0 0 24 24"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M5 12h11"
        stroke="currentColor"
        stroke-width="2.25"
        stroke-linecap="round"
      />
      <path
        d="M13 7l5 5-5 5"
        stroke="currentColor"
        stroke-width="2.25"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>
    <!-- Tur och retur: pilar åt båda håll -->
    <svg
      v-else
      viewBox="0 0 24 24"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M4 8h11"
        stroke="currentColor"
        stroke-width="2.25"
        stroke-linecap="round"
      />
      <path
        d="M12 4l4 4-4 4"
        stroke="currentColor"
        stroke-width="2.25"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
      <path
        d="M20 16H9"
        stroke="currentColor"
        stroke-width="2.25"
        stroke-linecap="round"
      />
      <path
        d="M12 20l-4-4 4-4"
        stroke="currentColor"
        stroke-width="2.25"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>
  </span>
</template>

<style scoped>
.mrt-trip-type-icon {
  display: inline-flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  width: 1.35rem;
  height: 1.35rem;
  line-height: 0;
}

.mrt-trip-type-icon svg {
  display: block;
  width: 1.35rem;
  height: 1.35rem;
}
</style>
