<script setup lang="ts">
import type { MonthDayMeta } from '../../config/types';

const props = defineProps<{
  day: number;
  info: MonthDayMeta;
  showCounts: boolean;
  selected: boolean;
}>();

const emit = defineEmits<{ click: [ymd: string] }>();

function onClick(): void {
  if (props.info.running && props.info.ymd) {
    emit('click', props.info.ymd);
  }
}
</script>

<template>
  <template v-if="!info.running">
    <span class="mrt-daynum">{{ day }}</span>
  </template>
  <button
    v-else
    type="button"
    class="mrt-day mrt-running mrt-day-clickable mrt-cursor-pointer"
    :class="{ 'is-selected': selected }"
    :aria-pressed="selected"
    @click="onClick"
  >
    <span class="mrt-daynum" aria-hidden="true">{{ day }}</span>
    <span class="mrt-dot" aria-hidden="true">{{ showCounts ? info.count : '•' }}</span>
  </button>
</template>
