<script setup lang="ts">
import { AdminDateList, AdminInlineForm, AdminPanel } from '../ui';
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
    <p v-if="datesDirty" class="notice notice-warning mrt-admin-unsaved">
      {{ adminStr(cfg, 'editorDatesUnsaved') }}
    </p>
    <AdminInlineForm v-if="canManage">
      <input v-model="dateInput" type="date" />
      <button type="button" class="button" @click="emit('add')">
        {{ adminStr(cfg, 'editorDatesAdd') }}
      </button>
      <button type="button" class="button button-primary" @click="emit('save')">
        {{ adminStr(cfg, 'save') }}
      </button>
    </AdminInlineForm>
    <AdminDateList>
      <li v-for="d in dates" :key="d">
        <span>{{ d }}</span>
        <button v-if="canManage" type="button" class="button-link" @click="emit('remove', d)">
          {{ adminStr(cfg, 'delete') }}
        </button>
      </li>
    </AdminDateList>
  </AdminPanel>
</template>
