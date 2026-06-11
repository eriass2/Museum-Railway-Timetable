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

function isDayCell(cell: WizardCalCell): cell is WizardDayCell {
  return cell.kind === 'day';
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
      <MrtWizardCalendarDayCell
        v-if="isDayCell(cell as WizardCalCell)"
        :day="(cell as WizardDayCell).day"
        :ymd="(cell as WizardDayCell).ymd"
        :status="(cell as WizardDayCell).status"
        :type="(cell as WizardDayCell).type"
        :selected="selectedYmd === (cell as WizardDayCell).ymd"
        :ariaLabel="dayAria((cell as WizardDayCell).ymd, (cell as WizardDayCell).status)"
        @pick="emit('pick', $event)"
      />
    </template>
  </MrtCalendarGrid>
</template>
