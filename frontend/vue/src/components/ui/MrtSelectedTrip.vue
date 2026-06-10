<script setup lang="ts">
defineProps<{
  /** Keeps return-step wizard overrides in journey-wizard CSS. */
  returnSummary?: boolean;
}>();
</script>

<template>
  <div
    class="mrt-selected-trip"
    :data-wizard-return-summary="returnSummary ? '' : undefined"
  >
    <div class="mrt-selected-trip__label">
      <slot name="label" />
    </div>
    <div class="mrt-selected-trip__card">
      <slot />
    </div>
  </div>
</template>
