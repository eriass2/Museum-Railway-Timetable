<script setup lang="ts">
import { useTemplateRef } from 'vue';
import type { TimetableTimeCellEdit } from '../../../types/timetableOverview';
import type { OverviewGridEdit } from '../../composables/timetable-editor/useOverviewGridEdit';
import { useOverviewGridCellDialog } from '../../composables/timetable-editor/useOverviewGridCellDialog';
import { adminConfig } from '../../types';
import { adminFmtN, adminStr } from '../../utils/adminLabels';
import { MrtButton } from '../ui';
import StopTimeModeSelect from './StopTimeModeSelect.vue';
import {
  gridRowShowsArrival,
  gridRowShowsDeparture,
} from '../../utils/timetable-editor/gridCellEdit';
import MrtOverviewTimeDisplay from '../../../components/overview/MrtOverviewTimeDisplay.vue';

const props = defineProps<{
  displayText: string;
  stationLabel: string;
  serviceNumber: string;
  rowKind: string;
  serviceId: number;
  stationId: number;
  edit: TimetableTimeCellEdit | undefined;
  editor: OverviewGridEdit;
}>();

const emit = defineEmits<{ saved: [] }>();

const cfg = adminConfig();
const dialogRef = useTemplateRef<HTMLDialogElement>('dialog');
const { draft, saving, openDialog, closeDialog, save } = useOverviewGridCellDialog(
  props,
  dialogRef,
  () => emit('saved'),
);
</script>

<template>
  <button
    type="button"
    class="mrt-ov-cell-trigger"
    :class="{ 'mrt-ov-cell-trigger--approximate': edit?.approximateTime }"
    @click="openDialog"
  >
    <MrtOverviewTimeDisplay :text="displayText || '—'" :approximate-time="edit?.approximateTime" />
  </button>
  <dialog ref="dialog" class="mrt-ov-cell-dialog" @cancel.prevent="closeDialog">
    <form class="mrt-ov-cell-dialog__form" @submit.prevent="save">
      <h4 class="mrt-ov-cell-dialog__title">
        {{
          adminFmtN(cfg, 'stopTimesGridEditTitle', {
            1: stationLabel,
            2: serviceNumber,
          })
        }}
      </h4>
      <label class="mrt-ov-cell-dialog__field mrt-ov-cell-dialog__field--check">
        <input v-model="draft.stopsHere" type="checkbox" />
        {{ adminStr(cfg, 'stopTimesColStops') }}
      </label>
      <p class="description mrt-ov-cell-dialog__empty-hint">
        {{ adminStr(cfg, 'editorGridEmptyCellHint') }}
      </p>
      <label v-if="gridRowShowsArrival(rowKind)" class="mrt-ov-cell-dialog__field">
        <span>{{ adminStr(cfg, 'stopTimesColArrival') }}</span>
        <input
          v-model="draft.arrival"
          type="time"
          class="mrt-input"
          :disabled="!draft.stopsHere"
          @change="draft.stopsHere = true"
        />
      </label>
      <label v-if="gridRowShowsDeparture(rowKind)" class="mrt-ov-cell-dialog__field">
        <span>{{ adminStr(cfg, 'stopTimesColDeparture') }}</span>
        <input
          v-model="draft.departure"
          type="time"
          class="mrt-input"
          :disabled="!draft.stopsHere"
          @change="draft.stopsHere = true"
        />
      </label>
      <label class="mrt-ov-cell-dialog__field mrt-ov-cell-dialog__field--check">
        <input v-model="draft.approximateTime" type="checkbox" :disabled="!draft.stopsHere" />
        {{ adminStr(cfg, 'stopTimesApproximateLabel') }}
      </label>
      <div class="mrt-ov-cell-dialog__pa">
        <StopTimeModeSelect
          v-model="draft.pickupMode"
          kind="pickup"
          :disabled="!draft.stopsHere"
        />
        <StopTimeModeSelect
          v-model="draft.dropoffMode"
          kind="dropoff"
          :disabled="!draft.stopsHere"
        />
      </div>
      <div class="mrt-ov-cell-dialog__actions">
        <MrtButton context="admin" type="button" variant="secondary" @click="closeDialog">
          {{ adminStr(cfg, 'cancel') }}
        </MrtButton>
        <MrtButton context="admin" type="submit" variant="primary" :disabled="saving">
          {{ adminStr(cfg, 'save') }}
        </MrtButton>
      </div>
    </form>
  </dialog>
</template>

<style scoped>
.mrt-ov-cell-trigger {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  margin: 0;
  padding: 0.15rem 0.25rem;
  border: 1px solid transparent;
  border-radius: 0.2rem;
  background: transparent;
  color: inherit;
  font: inherit;
  font-weight: 700;
  line-height: 1;
  white-space: nowrap;
  cursor: pointer;
  text-align: center;
}

.mrt-ov-cell-trigger--approximate {
  font-weight: 400;
}

.mrt-ov-cell-trigger:hover,
.mrt-ov-cell-trigger:focus-visible {
  border-color: var(--mrt-color-neutral-400, #999);
  background: var(--mrt-color-neutral-100, #f0f0f0);
}

.mrt-ov-cell-dialog {
  max-width: 22rem;
  padding: 0;
  border: 1px solid var(--mrt-color-neutral-300, #ccc);
  border-radius: 0.35rem;
}

.mrt-ov-cell-dialog::backdrop {
  background: rgb(0 0 0 / 35%);
}

.mrt-ov-cell-dialog__form {
  display: grid;
  gap: 0.75rem;
  padding: 1rem 1.1rem;
}

.mrt-ov-cell-dialog__title {
  margin: 0;
  font-size: 1rem;
  font-weight: 800;
  line-height: 1.3;
}

.mrt-ov-cell-dialog__empty-hint {
  margin: 0;
  font-size: 0.9rem;
}

.mrt-ov-cell-dialog__field {
  display: grid;
  gap: 0.25rem;
  font-weight: 600;
}

.mrt-ov-cell-dialog__pa {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem 1.25rem;
}

.mrt-ov-cell-dialog__actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 0.25rem;
}
</style>
