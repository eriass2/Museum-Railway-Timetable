<script setup lang="ts">
import { computed } from 'vue';
import MrtAsyncState from './MrtAsyncState.vue';
import MrtSurfaceCard from './MrtSurfaceCard.vue';

const props = withDefaults(
  defineProps<{
    loading?: boolean;
    error?: string;
    loadingText?: string;
    visible?: boolean;
    surface?: boolean;
  }>(),
  { visible: true, surface: false },
);

const wrapperTag = computed(() => (props.surface ? MrtSurfaceCard : 'div'));
</script>

<template>
  <component
    :is="wrapperTag"
    class="mrt-html-panel"
    :class="{ 'mrt-hidden': !visible }"
    :box="surface"
    role="region"
    aria-live="polite"
    tabindex="-1"
  >
    <MrtAsyncState :loading="loading" :error="error" :loading-text="loadingText">
      <slot />
    </MrtAsyncState>
  </component>
</template>
