<script setup lang="ts">
import type { PricesPayload } from '../../api/adminRest';
import { AdminDisclosure, AdminTableScroll, MrtButton } from '../ui';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import { useTicketCopyNotes } from '../../composables/prices/useTicketCopyNotes';
import PricesTicketCopyNoteRow from './PricesTicketCopyNoteRow.vue';

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
          <PricesTicketCopyNoteRow
            v-for="note in notes"
            :key="note.id"
            :note="note"
            :condition-options="conditionOptions"
            @update="updateNote(note.id, $event)"
            @remove="removeNote(note.id)"
          />
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
