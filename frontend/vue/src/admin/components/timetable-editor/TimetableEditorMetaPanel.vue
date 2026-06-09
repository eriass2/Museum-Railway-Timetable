<script setup lang="ts">
import { AdminFormActions, AdminPanel, AdminUnsavedBanner, MrtButton } from '../ui';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

defineProps<{
  metaDirty: boolean;
  timetableTypes: readonly { value: string; label: string }[];
}>();

const editTitle = defineModel<string>('editTitle', { required: true });
const editType = defineModel<string>('editType', { required: true });

const emit = defineEmits<{
  save: [];
  remove: [];
}>();

const cfg = adminConfig();
</script>

<template>
  <AdminPanel class="mrt-admin-timetable-meta">
    <AdminUnsavedBanner :show="metaDirty" :message="adminStr(cfg, 'editorMetaUnsaved')" />
    <h2 class="screen-reader-text">{{ adminStr(cfg, 'editorTitle') }}</h2>
    <p>
      <label for="mrt-tt-title">{{ adminStr(cfg, 'editorTitleLabel') }}</label>
      <input id="mrt-tt-title" v-model="editTitle" type="text" class="regular-text" />
    </p>
    <p>
      <label for="mrt-tt-type">{{ adminStr(cfg, 'editorTypeLabel') }}</label>
      <select id="mrt-tt-type" v-model="editType">
        <option v-for="opt in timetableTypes" :key="opt.value" :value="opt.value">
          {{ opt.label }}
        </option>
      </select>
      <span class="description">{{ adminStr(cfg, 'editorTypeHint') }}</span>
    </p>
    <AdminFormActions>
      <MrtButton context="admin" variant="primary" @click="emit('save')">
        {{ adminStr(cfg, 'editorSaveMeta') }}
      </MrtButton>
      <MrtButton context="admin" variant="link-delete" @click="emit('remove')">
        {{ adminStr(cfg, 'editorDeleteTimetable') }}
      </MrtButton>
    </AdminFormActions>
  </AdminPanel>
</template>
