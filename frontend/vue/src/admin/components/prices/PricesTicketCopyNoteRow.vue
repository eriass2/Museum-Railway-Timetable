<script setup lang="ts">
import type { TicketCopyCondition, TicketCopyNote } from '../../../shared/ticketCopy';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import { MrtButton } from '../ui';

defineProps<{
  note: TicketCopyNote;
  conditionOptions: { value: TicketCopyCondition; label: string }[];
}>();

const emit = defineEmits<{
  update: [patch: Partial<Pick<TicketCopyNote, 'condition' | 'text' | 'enabled'>>];
  remove: [];
}>();

const cfg = adminConfig();
</script>

<template>
  <tr>
    <td :data-label="adminStr(cfg, 'pricesTicketCopyCondCol', 'Villkor')">
      <select
        :value="note.condition"
        @change="emit('update', { condition: ($event.target as HTMLSelectElement).value as TicketCopyCondition })"
      >
        <option v-for="opt in conditionOptions" :key="opt.value" :value="opt.value">
          {{ opt.label }}
        </option>
      </select>
    </td>
    <td :data-label="adminStr(cfg, 'pricesTicketCopyTextCol', 'Text')">
      <textarea
        :value="note.text"
        class="large-text"
        rows="2"
        @input="emit('update', { text: ($event.target as HTMLTextAreaElement).value })"
      />
    </td>
    <td :data-label="adminStr(cfg, 'pricesTicketCopyEnabledCol', 'Aktiv')">
      <input
        type="checkbox"
        :checked="note.enabled"
        @change="emit('update', { enabled: ($event.target as HTMLInputElement).checked })"
      />
    </td>
    <td>
      <MrtButton context="admin" variant="link-delete" @click="emit('remove')">
        {{ adminStr(cfg, 'delete') }}
      </MrtButton>
    </td>
  </tr>
</template>
