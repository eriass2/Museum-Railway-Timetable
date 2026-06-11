import { computed, type Ref } from 'vue';
import type { PricesPayload } from '../../api/adminRest';
import type { TicketCopyCondition, TicketCopyNote } from '../../../shared/ticketCopy';
import type { AdminClientConfig } from '../../types';
import { adminStr } from '../../utils/adminLabels';

function createTicketCopyNote(index: number): TicketCopyNote {
  return {
    id: `note_${index}`,
    condition: 'always',
    text: '',
    enabled: true,
  };
}

export function useTicketCopyNotes(payload: Ref<PricesPayload>, cfg: AdminClientConfig) {
  const notes = computed({
    get: () => payload.value.ticket_copy_notes ?? [],
    set: (value: TicketCopyNote[]) => {
      payload.value = { ...payload.value, ticket_copy_notes: value };
    },
  });

  const conditionOptions = computed((): { value: TicketCopyCondition; label: string }[] => [
    { value: 'always', label: adminStr(cfg, 'pricesTicketCopyCondAlways', 'Alltid') },
    {
      value: 'afternoon',
      label: adminStr(cfg, 'pricesTicketCopyCondAfternoon', 'Eftermiddagsbiljett'),
    },
    {
      value: 'day_ticket',
      label: adminStr(cfg, 'pricesTicketCopyCondDay', 'Heldagsbiljett visas'),
    },
  ]);

  function addNote(): void {
    notes.value = [...notes.value, createTicketCopyNote(notes.value.length + 1)];
  }

  function removeNote(id: string): void {
    notes.value = notes.value.filter((note) => note.id !== id);
  }

  function updateNote(id: string, patch: Partial<TicketCopyNote>): void {
    notes.value = notes.value.map((note) => (note.id === id ? { ...note, ...patch } : note));
  }

  return { notes, conditionOptions, addNote, removeNote, updateNote };
}
