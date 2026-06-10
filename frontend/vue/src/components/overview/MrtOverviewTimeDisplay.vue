<script setup lang="ts">
import { computed } from 'vue';
import { parseOverviewTimeText } from '../../utils/overviewTimeDisplay';

const props = defineProps<{
  text: string;
  approximateTime?: boolean;
  cancelled?: boolean;
}>();

const parts = computed(() => parseOverviewTimeText(props.text));
</script>

<template>
  <span
    class="mrt-ov-time"
    :class="{
      'mrt-ov-time--cancelled': cancelled,
      'mrt-ov-time--approximate': approximateTime,
    }"
  >
    <template v-for="(label, index) in parts.labels" :key="`${label}-${index}`">
      <span class="mrt-ov-time__label">{{ label }}</span>
      <span class="mrt-ov-time__sep" aria-hidden="true">&nbsp;</span>
    </template>
    <span class="mrt-ov-time__value">{{ parts.value }}</span>
  </span>
</template>
