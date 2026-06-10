<script setup lang="ts">
import { computed } from 'vue';
import { formatOverviewTimePrefix, parseOverviewTimeText } from '../../utils/overviewTimeDisplay';

const props = defineProps<{
  text: string;
  approximateTime?: boolean;
  cancelled?: boolean;
}>();

const parts = computed(() => parseOverviewTimeText(props.text));
const prefix = computed(() => formatOverviewTimePrefix(parts.value, props.approximateTime));
</script>

<template>
  <span
    class="mrt-ov-time"
    :class="{
      'mrt-ov-time--cancelled': cancelled,
      'mrt-ov-time--approximate': approximateTime,
    }"
  >
    <span v-if="prefix" class="mrt-ov-time__prefix">{{ prefix }}</span
    ><span class="mrt-ov-time__value">{{ parts.value }}</span>
  </span>
</template>
