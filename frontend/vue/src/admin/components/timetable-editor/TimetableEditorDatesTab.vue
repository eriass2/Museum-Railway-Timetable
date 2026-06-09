<script setup lang="ts">
import { AdminDateList, AdminInlineForm, AdminPanel, AdminUnsavedBanner, MrtButton } from '../ui';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

defineProps<{
  canManage: boolean;
  datesDirty: boolean;
  dates: string[];
}>();

const cfg = adminConfig();
const dateInput = defineModel<string>('dateInput', { required: true });

const emit = defineEmits<{
  add: [];
  remove: [date: string];
  save: [];
}>();
</script>

<template>
  <AdminPanel>
    <AdminUnsavedBanner :show="datesDirty" :message="adminStr(cfg, 'editorDatesUnsaved')" />
    <AdminInlineForm v-if="canManage">
      <input v-model="dateInput" type="date" />
      <MrtButton context="admin" variant="secondary" @click="emit('add')">
        {{ adminStr(cfg, 'editorDatesAdd') }}
      </MrtButton>
      <MrtButton context="admin" variant="primary" @click="emit('save')">
        {{ adminStr(cfg, 'save') }}
      </MrtButton>
    </AdminInlineForm>
    <p v-if="canManage" class="description">{{ adminStr(cfg, 'editorDatesBatchHint') }}</p>
    <AdminDateList>
      <li v-for="d in dates" :key="d">
        <span>{{ d }}</span>
        <MrtButton v-if="canManage" context="admin" variant="link" @click="emit('remove', d)">
          {{ adminStr(cfg, 'delete') }}
        </MrtButton>
      </li>
    </AdminDateList>
  </AdminPanel>
</template>
