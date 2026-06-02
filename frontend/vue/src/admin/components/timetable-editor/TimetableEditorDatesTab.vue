<script setup lang="ts">
import { AdminDateList, AdminInlineForm, AdminPanel } from '../ui';

defineProps<{
  canManage: boolean;
  datesDirty: boolean;
  dates: string[];
}>();

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
      Osparade trafikdagar — klicka «Spara» för att spara listan.
    </p>
    <AdminInlineForm v-if="canManage">
      <input v-model="dateInput" type="date" />
      <button type="button" class="button" @click="emit('add')">Lägg till datum</button>
      <button type="button" class="button button-primary" @click="emit('save')">Spara</button>
    </AdminInlineForm>
    <AdminDateList>
      <li v-for="d in dates" :key="d">
        <span>{{ d }}</span>
        <button v-if="canManage" type="button" class="button-link" @click="emit('remove', d)">
          Ta bort
        </button>
      </li>
    </AdminDateList>
  </AdminPanel>
</template>
