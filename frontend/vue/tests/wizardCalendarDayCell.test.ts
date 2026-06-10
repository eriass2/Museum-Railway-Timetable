import { describe, expect, it } from 'vitest';
import type { WizardCalCell } from '../src/wizard/utils/wizardCalendarGrid';

function asWizardDay(cell: WizardCalCell) {
  return cell.kind === 'day' ? cell : null;
}

describe('wizard calendar day cell', () => {
  it('extracts day cells from grid rows', () => {
    const row: WizardCalCell[] = [
      { kind: 'pad' },
      { kind: 'day', day: 15, ymd: '2026-05-15', status: 'ok' },
    ];
    const day = asWizardDay(row[1]);
    expect(day?.status).toBe('ok');
    expect(day?.ymd).toBe('2026-05-15');
  });
});
