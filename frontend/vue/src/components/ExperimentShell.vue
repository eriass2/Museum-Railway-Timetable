<script setup lang="ts">
defineProps<{
  title: string;
  app: string;
  config: Record<string, unknown>;
}>();
</script>

<template>
  <section class="mrt-vue-experiment">
    <p class="mrt-vue-experiment__badge">Vue experiment</p>
    <h2 class="mrt-vue-experiment__title">{{ title }}</h2>
    <p class="mrt-vue-experiment__lede">
      Placeholder — legacy PHP/JS is bypassed when <code>MRT_VUE_FRONTEND</code> is on.
      Next step: port UI into this component.
    </p>
    <details class="mrt-vue-experiment__config">
      <summary>Shortcode config (JSON)</summary>
      <pre>{{ JSON.stringify(config, null, 2) }}</pre>
    </details>
  </section>
</template>
