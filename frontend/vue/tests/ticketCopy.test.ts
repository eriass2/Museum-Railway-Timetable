import { describe, expect, it } from 'vitest';
import {
  afternoonClockFromPriceNote,
  resolveTicketCopyFootnotes,
  ticketCopyNoteMatches,
} from '../src/shared/ticketCopy';

describe('ticketCopy', () => {
  it('matches conditions', () => {
    expect(ticketCopyNoteMatches('always', false, false)).toBe(true);
    expect(ticketCopyNoteMatches('afternoon', true, false)).toBe(true);
    expect(ticketCopyNoteMatches('afternoon', false, false)).toBe(false);
    expect(ticketCopyNoteMatches('day_ticket', false, true)).toBe(true);
  });

  it('resolves footnotes with afternoon clock placeholder', () => {
    const notes = resolveTicketCopyFootnotes(
      [
        { id: 'a', condition: 'always', text: 'Student-ID.', enabled: true },
        { id: 'b', condition: 'afternoon', text: 'Efter kl %1$s.', enabled: true },
        { id: 'c', condition: 'day_ticket', text: 'Heldags.', enabled: true },
      ],
      { isAfternoon: true, hasDayTicket: false, afternoonClock: '14:30' },
    );
    expect(notes).toEqual(['Student-ID.', 'Efter kl 14:30.']);
  });

  it('extracts clock from afternoon note', () => {
    expect(
      afternoonClockFromPriceNote('Eftermiddagsbiljett gäller tur och retur med avgång vid eller efter kl 15:00.'),
    ).toBe('15:00');
  });
});
