<script setup lang="ts">
import type { PricesPayload } from '../../api/adminRest';
import type { TicketCopyCondition } from '../../../shared/ticketCopy';
import { AdminDisclosure, AdminTableScroll, MrtButton } from '../ui';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import { useTicketCopyNotes } from '../../composables/prices/useTicketCopyNotes';

const payload = defineModel<PricesPayload>({ required: true });

const cfg = adminConfig();
const { notes, conditionOptions, addNote, removeNote, updateNote } = useTicketCopyNotes(payload, cfg);
</script>

<template>
  <AdminDisclosure :summary="adminStr(cfg, 'pricesTicketCopyHeading', 'Biljettcopy (fotnoter)')">
    <p class="description">
      {{ adminStr(cfg, 'pricesTicketCopyHint') }}
    </p>

    <AdminTableScroll>
      <table class="widefat striped mrt-admin-prices-ticket-copy__table mrt-admin-responsive-table">
        <thead>
          <tr>
            <th>{{ adminStr(cfg, 'pricesTicketCopyCondCol', 'Villkor') }}</th>
            <th>{{ adminStr(cfg, 'pricesTicketCopyTextCol', 'Text') }}</th>
            <th>{{ adminStr(cfg, 'pricesTicketCopyEnabledCol', 'Aktiv') }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="note in notes" :key="note.id">
            <td :data-label="adminStr(cfg, 'pricesTicketCopyCondCol', 'Villkor')">
              <select
                :value="note.condition"
                @change="updateNote(note.id, { condition: ($event.target as HTMLSelectElement).value as TicketCopyCondition })"
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
                @input="updateNote(note.id, { text: ($event.target as HTMLTextAreaElement).value })"
              />
            </td>
            <td :data-label="adminStr(cfg, 'pricesTicketCopyEnabledCol', 'Aktiv')">
              <input
                type="checkbox"
                :checked="note.enabled"
                @change="updateNote(note.id, { enabled: ($event.target as HTMLInputElement).checked })"
              />
            </td>
            <td>
              <MrtButton context="admin" variant="link-delete" @click="removeNote(note.id)">
                {{ adminStr(cfg, 'delete') }}
              </MrtButton>
            </td>
          </tr>
        </tbody>
      </table>
    </AdminTableScroll>

    <p>
      <MrtButton context="admin" variant="secondary" type="button" @click="addNote">
        {{ adminStr(cfg, 'pricesTicketCopyAdd', 'Lägg till fotnot') }}
      </MrtButton>
    </p>
  </AdminDisclosure>
</template>

<style scoped>
.mrt-admin-prices-ticket-copy__table textarea {
  width: 100%;
  min-width: 240px;
}
</style>
