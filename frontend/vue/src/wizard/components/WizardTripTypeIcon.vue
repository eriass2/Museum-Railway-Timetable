<script setup lang="ts">
defineProps<{
  variant: 'single' | 'return';
}>();
</script>

<template>
  <span class="mrt-journey-wizard__trip-type-icon" aria-hidden="true">
    <!-- Enkel resa: en pil åt höger -->
    <svg
      v-if="variant === 'single'"
      class="mrt-journey-wizard__trip-type-svg"
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
      class="mrt-journey-wizard__trip-type-svg"
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
