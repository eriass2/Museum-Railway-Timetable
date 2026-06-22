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

<style scoped>
@import './overviewStatus.css';

.mrt-ov-time {
  display: inline-flex;
  align-items: baseline;
  flex-wrap: nowrap;
  max-width: 100%;
  font-weight: 400;
  line-height: 1;
  white-space: nowrap;
}

.mrt-ov-time__ca,
.mrt-ov-time__suffix {
  flex: 0 0 auto;
  font-size: var(--mrt-ov-footnote-size, 0.5rem);
  font-weight: 400;
  line-height: 1;
}

.mrt-ov-time__ca {
  margin-right: 0.06em;
}

.mrt-ov-time__suffix {
  margin-left: 0.06em;
}

.mrt-ov-time__value {
  flex: 0 1 auto;
  font-size: 1em;
  font-weight: inherit;
}

@container (max-width: 3.5rem) {
  .mrt-ov-time {
    font-size: 0.9em;
  }

  .mrt-ov-time__ca,
  .mrt-ov-time__suffix {
    font-size: calc(var(--mrt-ov-footnote-size, 0.5rem) * 0.88);
  }
}
</style>
