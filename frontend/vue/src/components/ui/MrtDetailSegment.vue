<script setup lang="ts">
import MrtHeading from './MrtHeading.vue';

defineProps<{
  title?: string;
  notice?: string;
  noticeLabel?: string;
  transferText?: string;
}>();
</script>

<template>
  <div class="mrt-detail-segment mrt-mb-sm">
    <MrtHeading v-if="title" level="h4" size="md" class="mrt-detail-segment__title">
      {{ title }}
    </MrtHeading>
    <p v-if="notice" class="mrt-detail-segment__notice">
      <strong v-if="noticeLabel">{{ noticeLabel }}:</strong> {{ notice }}
    </p>
    <div v-if="$slots.meta" class="mrt-detail-segment__meta">
      <slot name="meta" />
    </div>
    <slot />
    <p v-if="transferText" class="mrt-detail-segment__transfer">
      {{ transferText }}
    </p>
  </div>
</template>
