<script setup lang="ts">
import { computed } from 'vue';
import { formatOverviewTimePrefix, formatOverviewTimeValue, parseOverviewTimeText } from '../../utils/overviewTimeDisplay';

const props = defineProps<{
  text: string;
  approximateTime?: boolean;
  cancelled?: boolean;
}>();

const parts = computed(() => parseOverviewTimeText(props.text));
const prefix = computed(() => formatOverviewTimePrefix(parts.value));
const timeValue = computed(() => formatOverviewTimeValue(parts.value, props.approximateTime));
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
    ><span class="mrt-ov-time__value">{{ timeValue }}</span>
  </span>
</template>
