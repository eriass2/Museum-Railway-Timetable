<script setup lang="ts">
import { computed } from 'vue';
import {
  formatOverviewTimeSuffix,
  formatOverviewTimeValue,
  parseOverviewTimeText,
} from '../../utils/overviewTimeDisplay';

const props = defineProps<{
  text: string;
  approximateTime?: boolean;
  cancelled?: boolean;
}>();

const parts = computed(() => parseOverviewTimeText(props.text));
const showCa = computed(() => props.approximateTime || parts.value.approximate);
const timeDigits = computed(() => formatOverviewTimeValue(parts.value, props.approximateTime).replace(/^Ca\s+/, ''));
const suffix = computed(() => formatOverviewTimeSuffix(parts.value));
</script>

<template>
  <span
    class="mrt-ov-time"
    :class="{
      'mrt-ov-time--cancelled': cancelled,
      'mrt-ov-time--approximate': showCa,
    }"
  >
    <span v-if="showCa" class="mrt-ov-time__ca">Ca</span
    ><span class="mrt-ov-time__value">{{ timeDigits }}</span
    ><span v-if="suffix" class="mrt-ov-time__suffix">{{ suffix }}</span>
  </span>
</template>
