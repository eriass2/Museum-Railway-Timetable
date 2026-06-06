<script setup lang="ts">
import { AdminFormActions, MrtButton } from '../ui';
import TimetableTripFieldsBlock from './TimetableTripFieldsBlock.vue';
import type { TimetableTripDraft } from './tripFormTypes';
import type { TimetableDetail } from '../../types';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

export type TripEditDraft = TimetableTripDraft & { service_id: number };

withDefaults(
  defineProps<{
    detail: TimetableDetail;
    destinations: { id: number; name: string }[];
    trainTypeIconKey: (typeId: number) => string;
    embedded?: boolean;
  }>(),
  { embedded: false },
);

const draft = defineModel<TripEditDraft>('draft', { required: true });

const cfg = adminConfig();

const emit = defineEmits<{
  save: [];
  cancel: [];
  'route-change': [];
}>();
</script>

<template>
  <div :class="embedded ? 'mrt-admin-trip-edit--embedded' : 'mrt-admin-trip-edit'">
    <h3 v-if="!embedded" class="mrt-admin-trip-edit__title">
      {{ adminStr(cfg, 'editorEditTripTitle') }}
    </h3>
    <p v-else class="mrt-admin-trip-edit__title mrt-admin-trip-edit__title--embedded">
      {{ adminStr(cfg, 'editorEditTripTitle') }}
    </p>
    <div class="mrt-admin-trip-form">
      <TimetableTripFieldsBlock
        v-model:draft="draft"
        :detail="detail"
        :destinations="destinations"
        :field-id-prefix="`trip-edit-${draft.service_id}`"
        :train-type-icon-key="trainTypeIconKey"
        @route-change="emit('route-change')"
      />
      <AdminFormActions>
        <MrtButton context="admin" variant="primary" @click="emit('save')">
          {{ adminStr(cfg, 'editorSaveTrip') }}
        </MrtButton>
        <MrtButton context="admin" variant="secondary" @click="emit('cancel')">
          {{ adminStr(cfg, 'editorCancelEdit') }}
        </MrtButton>
      </AdminFormActions>
    </div>
  </div>
</template>

<style scoped>
.mrt-admin-trip-edit {
  margin-top: 12px;
  border: 1px solid #c3c4c7;
  padding: 12px;
}

.mrt-admin-trip-edit--embedded {
  margin-top: 0;
}

.mrt-admin-trip-edit__title {
  margin: 0 0 8px;
  font-size: 14px;
  font-weight: 600;
}

.mrt-admin-trip-edit__title--embedded {
  margin-bottom: 12px;
}
</style>
