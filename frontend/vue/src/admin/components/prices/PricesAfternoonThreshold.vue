<script setup lang="ts">
import { computed } from 'vue';
import { adminFmtN, adminStr } from '../../utils/adminLabels';
import { minutesToTimeInput, timeInputToMinutes } from '../../utils/settingsTime';
import { adminConfig } from '../../types';

const props = defineProps<{
  thresholdMinutes: number;
}>();

const emit = defineEmits<{
  'update:thresholdMinutes': [value: number];
}>();

const cfg = adminConfig();
const afternoonActive = computed(() => props.thresholdMinutes > 0);

function onThresholdInput(event: Event) {
  emit('update:thresholdMinutes', timeInputToMinutes((event.target as HTMLInputElement).value));
}
</script>

<template>
  <div class="mrt-admin-prices-afternoon__threshold">
    <label>
      {{ adminStr(cfg, 'pricesAfternoonThreshold') }}
      <input
        :value="minutesToTimeInput(thresholdMinutes)"
        type="time"
        @input="onThresholdInput"
      />
    </label>
    <p v-if="!afternoonActive" class="description mrt-admin-prices-afternoon__disabled">
      {{ adminStr(cfg, 'pricesAfternoonDisabledHint') }}
    </p>
    <p v-else class="description">
      {{
        adminFmtN(cfg, 'pricesAfternoonThresholdActive', {
          1: minutesToTimeInput(thresholdMinutes),
        })
      }}
    </p>
  </div>
</template>

<style scoped>
.mrt-admin-prices-afternoon__threshold label {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

@media (max-width: 782px) {
  .mrt-admin-prices-afternoon__threshold label {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
