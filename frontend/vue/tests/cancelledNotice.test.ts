import { describe, expect, it } from 'vitest';
import { connectionIsCancelled, isCancelledNotice } from '../src/shared/cancelledNotice';
import { overviewColumnIsCancelled } from '../src/shared/overviewCancelled';

describe('isCancelledNotice', () => {
  it('detects Swedish cancelled markers', () => {
    expect(isCancelledNotice('Inställd')).toBe(true);
    expect(isCancelledNotice('Inställd pga väder')).toBe(true);
    expect(isCancelledNotice('Försenad avgång')).toBe(false);
  });
});

describe('connectionIsCancelled', () => {
  it('prefers explicit API flag', () => {
    expect(connectionIsCancelled({ is_cancelled: true, notice: '' })).toBe(true);
  });

  it('falls back to notice text', () => {
    expect(connectionIsCancelled({ notice: 'Inställd' })).toBe(true);
  });
});

describe('overviewColumnIsCancelled', () => {
  it('uses column flag or notice', () => {
    expect(
      overviewColumnIsCancelled({
        serviceNumber: '71',
        trainTypeName: 'Diesel',
        trainTypeSlug: 'diesel',
        iconKey: 'diesel',
        isSpecial: false,
        specialName: '',
        highlightColor: '',
        isCancelled: true,
      }),
    ).toBe(true);
    expect(
      overviewColumnIsCancelled({
        serviceNumber: '71',
        trainTypeName: 'Diesel',
        trainTypeSlug: 'diesel',
        iconKey: 'diesel',
        isSpecial: false,
        specialName: '',
        highlightColor: '',
        deviationNotice: 'Inställd',
      }),
    ).toBe(true);
  });
});
