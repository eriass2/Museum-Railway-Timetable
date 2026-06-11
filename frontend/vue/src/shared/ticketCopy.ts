export type TicketCopyCondition = 'always' | 'afternoon' | 'day_ticket';

export type TicketCopyNote = {
  id: string;
  condition: TicketCopyCondition;
  text: string;
  enabled: boolean;
};

export function ticketCopyNoteMatches(
  condition: TicketCopyCondition,
  isAfternoon: boolean,
  hasDayTicket: boolean,
): boolean {
  if (condition === 'always') {
    return true;
  }
  if (condition === 'afternoon') {
    return isAfternoon;
  }
  if (condition === 'day_ticket') {
    return hasDayTicket;
  }
  return false;
}

/** Clock time (HH:MM) embedded in wizard `priceAfternoonNote`. */
export function afternoonClockFromPriceNote(note: string | undefined): string {
  if (!note) {
    return '';
  }
  const match = note.match(/kl\s+(\d{1,2}:\d{2})/i);
  return match?.[1] ?? '';
}

export function resolveTicketCopyFootnotes(
  notes: TicketCopyNote[],
  context: { isAfternoon: boolean; hasDayTicket: boolean; afternoonClock?: string },
): string[] {
  const clock = context.afternoonClock ?? '';
  const out: string[] = [];
  for (const note of notes) {
    if (!note.enabled) {
      continue;
    }
    const text = note.text.trim();
    if (text === '') {
      continue;
    }
    if (!ticketCopyNoteMatches(note.condition, context.isAfternoon, context.hasDayTicket)) {
      continue;
    }
    out.push(text.includes('%1$s') && clock !== '' ? text.replace('%1$s', clock) : text);
  }
  return out;
}
