<script setup lang="ts">
import MrtCalendarGrid from '../../components/ui/MrtCalendarGrid.vue';
import type { CalendarDayStatus } from '../types';
import type { WizardCalCell } from '../utils/wizardCalendarGrid';

type WizardDayCell = Extract<WizardCalCell, { kind: 'day' }>;

defineProps<{
  loading: boolean;
  weekdayHeaders: string[];
  gridRows: WizardCalCell[][];
  selectedYmd: string;
  gridLabel: string;
  loadingLabel: string;
  dayAria: (ymd: string, status: CalendarDayStatus) => string;
}>();

const emit = defineEmits<{ pick: [ymd: string] }>();

function asDay(cell: WizardCalCell): WizardDayCell | null {
  return cell.kind === 'day' ? cell : null;
}
</script>

<template>
  <MrtCalendarGrid
    variant="wizard"
    :weekday-headers="weekdayHeaders"
    :rows="gridRows"
    :grid-label="gridLabel"
    :loading="loading"
    :loading-label="loadingLabel"
  >
    <template #cell="{ cell }">
      <template v-if="(cell as WizardCalCell).kind !== 'day'" />
      <button
        v-else-if="asDay(cell as WizardCalCell)?.status === 'ok'"
        type="button"
        class="mrt-calendar-day mrt-calendar-day--ok"
        :class="{ 'is-selected': selectedYmd === asDay(cell as WizardCalCell)!.ymd }"
        :aria-label="dayAria(asDay(cell as WizardCalCell)!.ymd, asDay(cell as WizardCalCell)!.status)"
        :aria-pressed="selectedYmd === asDay(cell as WizardCalCell)!.ymd"
        @click="emit('pick', asDay(cell as WizardCalCell)!.ymd)"
      >
        {{ asDay(cell as WizardCalCell)!.day }}
      </button>
      <button
        v-else-if="asDay(cell as WizardCalCell)?.status === 'traffic_no_match'"
        type="button"
        class="mrt-calendar-day mrt-calendar-day--traffic"
        disabled
        :aria-label="dayAria(asDay(cell as WizardCalCell)!.ymd, asDay(cell as WizardCalCell)!.status)"
      >
        {{ asDay(cell as WizardCalCell)!.day }}
      </button>
      <button
        v-else-if="asDay(cell as WizardCalCell)"
        type="button"
        class="mrt-calendar-day mrt-calendar-day--none"
        disabled
        :aria-label="dayAria(asDay(cell as WizardCalCell)!.ymd, asDay(cell as WizardCalCell)!.status)"
      >
        {{ asDay(cell as WizardCalCell)!.day }}
      </button>
    </template>
  </MrtCalendarGrid>
</template>
