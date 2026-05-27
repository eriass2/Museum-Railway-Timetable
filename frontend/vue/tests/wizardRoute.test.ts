import { describe, expect, it, vi } from 'vitest';
import { validateWizardRoute } from '../src/wizard/store/wizardRoute';

describe('validateWizardRoute', () => {
  it('validates form ids before they are copied to store', () => {
    const showError = vi.fn();
    const state = { fromId: 0, toId: 0, error: '', showError };

    expect(validateWizardRoute(state, { app: 'wizard' }, {}, 12, 34)).toBe(true);
    expect(showError).not.toHaveBeenCalled();

    expect(validateWizardRoute(state, { app: 'wizard' }, {}, 0, 34)).toBe(false);
    expect(validateWizardRoute(state, { app: 'wizard' }, {}, 12, 12)).toBe(false);
  });
});
