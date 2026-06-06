<script setup lang="ts">
import { AdminDisclosure, AdminTrainTypeSelect } from '../ui';
import type { TimetableDetail } from '../../types';
import type { TimetableTripDraft } from './tripFormTypes';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

defineProps<{
  detail: TimetableDetail;
  destinations: { id: number; name: string }[];
  fieldIdPrefix: string;
  trainTypeIconKey: (typeId: number) => string;
}>();

const draft = defineModel<TimetableTripDraft>('draft', { required: true });

const cfg = adminConfig();

const emit = defineEmits<{
  'route-change': [];
}>();
</script>

<template>
  <p>
    <label :for="`${fieldIdPrefix}-num`">
      {{ adminStr(cfg, 'editorColServiceNumber') }}
    </label>
    <input
      :id="`${fieldIdPrefix}-num`"
      v-model="draft.service_number"
      type="text"
      class="regular-text"
    />
    <span class="description">{{ adminStr(cfg, 'editorServiceNumberHint') }}</span>
  </p>
  <select v-model.number="draft.route_id" @change="emit('route-change')">
    <option :value="0">{{ adminStr(cfg, 'editorRoutePrompt') }}</option>
    <option v-for="r in detail.routes" :key="r.id" :value="r.id">{{ r.title }}</option>
  </select>
  <AdminTrainTypeSelect
    v-model="draft.train_type_id"
    :train-types="detail.train_types"
    :icon-key="trainTypeIconKey(draft.train_type_id)"
    show-icon
    empty-label-key="editorTrainTypePrompt"
  />
  <select v-model.number="draft.end_station_id">
    <option :value="0">{{ adminStr(cfg, 'editorDestinationPrompt') }}</option>
    <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
  </select>
  <AdminDisclosure :summary="adminStr(cfg, 'editorHighlightSummary')">
    <p class="description">{{ adminStr(cfg, 'editorHighlightHint') }}</p>
    <p>
      <label :for="`${fieldIdPrefix}-hl-label`">
        {{ adminStr(cfg, 'editorHighlightLabel') }}
      </label>
      <input
        :id="`${fieldIdPrefix}-hl-label`"
        v-model="draft.highlight_label"
        type="text"
        class="regular-text"
      />
    </p>
    <p>
      <label :for="`${fieldIdPrefix}-hl-color`">
        {{ adminStr(cfg, 'editorHighlightColor') }}
      </label>
      <input
        :id="`${fieldIdPrefix}-hl-color`"
        v-model="draft.highlight_color"
        type="color"
        :disabled="!draft.highlight_label.trim()"
      />
      <input
        v-model="draft.highlight_color"
        type="text"
        class="regular-text mrt-admin-highlight-hex"
        pattern="#[0-9a-fA-F]{3,8}"
        placeholder="#fff9c4"
        :disabled="!draft.highlight_label.trim()"
      />
    </p>
    <p>
      <label :for="`${fieldIdPrefix}-hl-note`">
        {{ adminStr(cfg, 'editorHighlightNote') }}
      </label>
      <textarea
        :id="`${fieldIdPrefix}-hl-note`"
        v-model="draft.highlight_note"
        rows="2"
        class="large-text"
        :disabled="!draft.highlight_label.trim()"
      />
    </p>
  </AdminDisclosure>
</template>

<style scoped>
.mrt-admin-highlight-hex {
  margin-left: 8px;
  max-width: 7rem;
}
</style>
