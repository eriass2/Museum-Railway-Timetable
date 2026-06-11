import { describe, expect, it } from 'vitest';
import { lineKindLabelKey } from '../src/admin/utils/stations-routes/lineKindLabel';

describe('lineKindLabelKey', () => {
  it('maps known line kinds to l10n keys', () => {
    expect(lineKindLabelKey('main')).toBe('stationsLineKindMain');
    expect(lineKindLabelKey('branch')).toBe('stationsLineKindBranch');
    expect(lineKindLabelKey('pattern')).toBe('stationsLineKindPattern');
    expect(lineKindLabelKey('')).toBe('');
  });
});
