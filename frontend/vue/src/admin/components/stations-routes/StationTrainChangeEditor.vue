<script setup lang="ts">
import { toRef } from 'vue';
import { MrtButton } from '../ui';
import { useStationTrainChangeDraft } from '../../composables/stations-routes/useStationTrainChangeDraft';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import type { StationRow } from '../../types';

const props = defineProps<{
  station: StationRow;
}>();

const cfg = adminConfig();
const { draftEntries, warnings, updateEntry, addRow, removeRow } = useStationTrainChangeDraft(
  toRef(props, 'station'),
  cfg,
);
</script>

<template>
  <div class="mrt-admin-train-change">
    <p class="description">{{ adminStr(cfg, 'stationsTrainChangeHint') }}</p>
    <p class="description">{{ adminStr(cfg, 'stationsTrainChangeColumnHint') }}</p>
    <ul v-if="warnings.length" class="mrt-admin-train-change__warnings" role="alert">
      <li v-for="warning in warnings" :key="warning">{{ warning }}</li>
    </ul>
    <div
      v-for="(row, index) in draftEntries"
      :key="`${station.id}-tc-${index}`"
      class="mrt-admin-train-change__row"
    >
      <label class="screen-reader-text" :for="`tc-from-${station.id}-${index}`">
        {{ adminStr(cfg, 'stationsTrainChangeFrom') }}
      </label>
      <input
        :id="`tc-from-${station.id}-${index}`"
        :value="row.from_service"
        type="text"
        class="small-text"
        :placeholder="adminStr(cfg, 'stationsTrainChangeFromPh')"
        @input="updateEntry(index, { from_service: ($event.target as HTMLInputElement).value })"
      />
      <span aria-hidden="true">→</span>
      <label class="screen-reader-text" :for="`tc-type-${station.id}-${index}`">
        {{ adminStr(cfg, 'stationsTrainChangeType') }}
      </label>
      <input
        :id="`tc-type-${station.id}-${index}`"
        :value="row.type_name"
        type="text"
        class="regular-text"
        :placeholder="adminStr(cfg, 'stationsTrainChangeTypePh')"
        @input="updateEntry(index, { type_name: ($event.target as HTMLInputElement).value })"
      />
      <label class="screen-reader-text" :for="`tc-to-${station.id}-${index}`">
        {{ adminStr(cfg, 'stationsTrainChangeTo') }}
      </label>
      <input
        :id="`tc-to-${station.id}-${index}`"
        :value="row.to_service"
        type="text"
        class="small-text"
        :placeholder="adminStr(cfg, 'stationsTrainChangeToPh')"
        @input="updateEntry(index, { to_service: ($event.target as HTMLInputElement).value })"
      />
      <MrtButton context="admin" variant="link-delete" type="button" @click="removeRow(index)">
        {{ adminStr(cfg, 'delete') }}
      </MrtButton>
    </div>
    <MrtButton context="admin" variant="secondary" type="button" @click="addRow">
      {{ adminStr(cfg, 'stationsTrainChangeAdd') }}
    </MrtButton>
  </div>
</template>

<style scoped>
.mrt-admin-train-change__row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.35rem 0.5rem;
  margin-bottom: 0.5rem;
}

.mrt-admin-train-change__warnings {
  margin: 0 0 0.75rem 1.25rem;
  color: #8a5a00;
  font-weight: 700;
}
</style>
