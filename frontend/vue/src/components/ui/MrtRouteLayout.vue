<script setup lang="ts">
defineProps<{
  timetableHref?: string;
  timetableLabel?: string;
}>();
</script>

<template>
  <div class="mrt-route-layout">
    <div class="mrt-route-layout__stations">
      <slot name="stations" />
    </div>
    <slot />
    <p v-if="timetableHref" class="mrt-route-layout__link-wrap">
      <a
        class="mrt-route-layout__link"
        :href="timetableHref"
        target="_blank"
        rel="noopener noreferrer"
      >
        <slot name="timetable">{{ timetableLabel }}</slot>
      </a>
    </p>
  </div>
</template>
