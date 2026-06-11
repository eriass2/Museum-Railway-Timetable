<script setup lang="ts">
import type { StopTimeRow } from '../../types';
import StopTimeModeSelect from './StopTimeModeSelect.vue';

defineProps<{
  row: StopTimeRow;
  canEditTimes: boolean;
  canEditModes: boolean;
}>();
</script>

<template>
  <tr>
    <td>
      <input v-model="row.stops_here" type="checkbox" :disabled="!canEditTimes" />
    </td>
    <td>{{ row.name }}</td>
    <td>
      <input
        v-model="row.arrival_time"
        type="time"
        class="mrt-input"
        :disabled="!canEditTimes"
      />
    </td>
    <td>
      <input
        v-model="row.departure_time"
        type="time"
        class="mrt-input"
        :disabled="!canEditTimes"
      />
    </td>
    <td>
      <StopTimeModeSelect
        v-model="row.pickup_mode"
        kind="pickup"
        :disabled="!canEditModes"
      />
    </td>
    <td>
      <StopTimeModeSelect
        v-model="row.dropoff_mode"
        kind="dropoff"
        :disabled="!canEditModes"
      />
    </td>
    <td>
      <input v-model="row.approximate_time" type="checkbox" :disabled="!canEditTimes" />
    </td>
  </tr>
</template>
