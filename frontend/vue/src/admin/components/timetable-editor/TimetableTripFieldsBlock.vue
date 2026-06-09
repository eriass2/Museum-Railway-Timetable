<script setup lang="ts">
import { computed } from 'vue';
import { AdminDisclosure, AdminTrainTypeSelect } from '../ui';
import type { TimetableDetail } from '../../types';
import type { TimetableTripDraft } from './tripFormTypes';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

const props = defineProps<{
  detail: TimetableDetail;
  fieldIdPrefix: string;
  trainTypeIconKey: (typeId: number) => string;
}>();

const draft = defineModel<TimetableTripDraft>('draft', { required: true });

const cfg = adminConfig();

const routeSelectId = computed(() => `${props.fieldIdPrefix}-route`);
const serviceNumberInputId = computed(() => `${props.fieldIdPrefix}-num`);
const trainTypeSelectId = computed(() => `${props.fieldIdPrefix}-type`);

const selectedTrainTypeName = computed(
  () => props.detail.train_types.find((t) => t.id === draft.value.train_type_id)?.name ?? '',
);
</script>

<template>
  <div class="mrt-admin-trip-fields">
    <p class="mrt-admin-trip-fields__field">
      <label :for="routeSelectId">{{ adminStr(cfg, 'editorColRoute') }}</label>
      <select :id="routeSelectId" v-model.number="draft.route_id" class="widefat">
        <option :value="0">{{ adminStr(cfg, 'editorRoutePrompt') }}</option>
        <option v-for="r in detail.routes" :key="r.id" :value="r.id">{{ r.title }}</option>
      </select>
      <span class="description">{{ adminStr(cfg, 'editorDestinationAuto') }}</span>
    </p>

    <p class="mrt-admin-trip-fields__field">
      <label :for="serviceNumberInputId">{{ adminStr(cfg, 'editorColServiceNumber') }}</label>
      <input
        :id="serviceNumberInputId"
        v-model="draft.service_number"
        type="text"
        class="regular-text"
      />
      <span class="description">{{ adminStr(cfg, 'editorServiceNumberHint') }}</span>
    </p>

    <p class="mrt-admin-trip-fields__field">
      <label :for="trainTypeSelectId">{{ adminStr(cfg, 'editorColTrainType') }}</label>
      <AdminTrainTypeSelect
        v-model="draft.train_type_id"
        :select-id="trainTypeSelectId"
        :train-types="detail.train_types"
        :icon-key="trainTypeIconKey(draft.train_type_id)"
        :icon-label="selectedTrainTypeName"
        show-icon
        wide
        empty-label-key="editorTrainTypePrompt"
      />
    </p>

    <AdminDisclosure :summary="adminStr(cfg, 'editorHighlightSummary')">
      <p class="description">{{ adminStr(cfg, 'editorHighlightHint') }}</p>
      <p class="mrt-admin-trip-fields__field">
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
      <p class="mrt-admin-trip-fields__field">
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
      <p class="mrt-admin-trip-fields__field">
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
  </div>
</template>

<style scoped>
.mrt-admin-highlight-hex {
  margin-left: 8px;
  max-width: 7rem;
}
</style>
