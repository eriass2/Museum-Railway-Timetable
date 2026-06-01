<script setup lang="ts">
import MrtCalendarGrid from '../../components/ui/MrtCalendarGrid.vue';
import MrtWizardCalendarDayCell from '../../components/ui/MrtWizardCalendarDayCell.vue';
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
      <MrtWizardCalendarDayCell
        v-else-if="asDay(cell as WizardCalCell)"
        :day="asDay(cell as WizardCalCell)!.day"
        :ymd="asDay(cell as WizardCalCell)!.ymd"
        :status="asDay(cell as WizardCalCell)!.status"
        :type="asDay(cell as WizardCalCell)!.type"
        :selected="selectedYmd === asDay(cell as WizardCalCell)!.ymd"
        :ariaLabel="dayAria(asDay(cell as WizardCalCell)!.ymd, asDay(cell as WizardCalCell)!.status)"
        @pick="emit('pick', $event)"
      />
    </template>
  </MrtCalendarGrid>
</template>
