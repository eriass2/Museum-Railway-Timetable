<script setup lang="ts">
import { reactive, ref, useTemplateRef } from 'vue';
import type { TimetableTimeCellEdit } from '../../types/timetableOverview';
import type { OverviewGridEdit } from '../composables/useOverviewGridEdit';
import { adminConfig } from '../types';
import { adminFmtN, adminStr } from '../utils/adminLabels';
import { MrtButton } from './ui';
import StopTimePaCheckbox from './StopTimePaCheckbox.vue';

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
const saving = ref(false);
const draft = reactive<TimetableTimeCellEdit>(emptyDraft());

function emptyDraft(): TimetableTimeCellEdit {
  return {
    arrival: '',
    departure: '',
    stopsHere: false,
    pickupAllowed: true,
    dropoffAllowed: true,
  };
}

function showDeparture(kind: string): boolean {
  return kind === 'from' || kind === 'departure' || kind === 'station';
}

function showArrival(kind: string): boolean {
  return kind === 'to' || kind === 'arrival' || kind === 'station';
}

function resetDraft(): void {
  const base = props.editor.mergeEdit(props.serviceId, props.stationId, props.edit, {});
  draft.arrival = props.editor.hhmmToInput(base.arrival);
  draft.departure = props.editor.hhmmToInput(base.departure);
  draft.stopsHere = base.stopsHere;
  draft.pickupAllowed = base.pickupAllowed;
  draft.dropoffAllowed = base.dropoffAllowed;
}

function openDialog(): void {
  resetDraft();
  dialogRef.value?.showModal();
}

function closeDialog(): void {
  dialogRef.value?.close();
}

async function save(): Promise<void> {
  if (saving.value) {
    return;
  }
  saving.value = true;
  try {
    const payload: TimetableTimeCellEdit = {
      stopsHere: draft.stopsHere,
      arrival: props.editor.inputToHhmm(draft.arrival),
      departure: props.editor.inputToHhmm(draft.departure),
      pickupAllowed: draft.pickupAllowed,
      dropoffAllowed: draft.dropoffAllowed,
    };
    if (payload.stopsHere && showDeparture(props.rowKind) && payload.departure) {
      payload.stopsHere = true;
    }
    if (payload.stopsHere && showArrival(props.rowKind) && payload.arrival) {
      payload.stopsHere = true;
    }
    await props.editor.applyCellEdit(props.serviceId, props.stationId, payload);
    emit('saved');
    closeDialog();
  } finally {
    saving.value = false;
  }
}
</script>

<template>
  <button type="button" class="mrt-ov-cell-trigger" @click="openDialog">
    {{ displayText || '—' }}
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
      <label v-if="showArrival(rowKind)" class="mrt-ov-cell-dialog__field">
        <span>{{ adminStr(cfg, 'stopTimesColArrival') }}</span>
        <input
          v-model="draft.arrival"
          type="time"
          class="mrt-input"
          :disabled="!draft.stopsHere"
          @change="draft.stopsHere = true"
        />
      </label>
      <label v-if="showDeparture(rowKind)" class="mrt-ov-cell-dialog__field">
        <span>{{ adminStr(cfg, 'stopTimesColDeparture') }}</span>
        <input
          v-model="draft.departure"
          type="time"
          class="mrt-input"
          :disabled="!draft.stopsHere"
          @change="draft.stopsHere = true"
        />
      </label>
      <div class="mrt-ov-cell-dialog__pa">
        <StopTimePaCheckbox
          v-model="draft.pickupAllowed"
          kind="pickup"
          :disabled="!draft.stopsHere"
        />
        <StopTimePaCheckbox
          v-model="draft.dropoffAllowed"
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
  display: block;
  width: 100%;
  margin: 0;
  padding: 0.15rem 0.25rem;
  border: 1px solid transparent;
  border-radius: 0.2rem;
  background: transparent;
  color: inherit;
  font: inherit;
  font-weight: 700;
  cursor: pointer;
  text-align: center;
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
