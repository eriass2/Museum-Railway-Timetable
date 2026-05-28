<script setup lang="ts">
defineProps<{
  timeRange: string;
  route: string;
  date?: string;
  notice?: string;
  noticeWarn?: boolean;
}>();
</script>

<template>
  <div class="mrt-trip-summary">
    <p class="mrt-trip-summary__time">{{ timeRange }}</p>
    <p class="mrt-trip-summary__route">{{ route }}</p>
    <p v-if="date" class="mrt-trip-summary__date">{{ date }}</p>
    <p
      v-if="notice"
      class="mrt-trip-summary__notice"
      :class="{ 'mrt-trip-summary__notice--warn': noticeWarn }"
    >
      {{ notice }}
    </p>
  </div>
</template>
