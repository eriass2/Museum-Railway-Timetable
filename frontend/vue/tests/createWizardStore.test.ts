import { describe, expect, it } from 'vitest';
import { createWizardStore } from '../src/wizard/store/createWizardStore';
import type { WizardVueConfig } from '../src/config/types';

function minimalWizardConfig(): WizardVueConfig {
  return {
    app: 'wizard',
    stations: [
      { id: 1, title: 'A' },
      { id: 2, title: 'B' },
    ],
    wizard: {
      pleaseStations: 'Välj stationer',
      stepRoute: 'Sök',
    },
    labels: {},
  };
}

describe('createWizardStore', () => {
  it('starts on route step with empty selection', () => {
    const { store } = createWizardStore(minimalWizardConfig());
    expect(store.step).toBe('route');
    expect(store.fromId).toBe(0);
    expect(store.outbound).toBeNull();
  });

  it('clears and shows errors', () => {
    const { store } = createWizardStore(minimalWizardConfig());
    store.showError('Fel');
    expect(store.error).toBe('Fel');
    store.clearError();
    expect(store.error).toBe('');
  });

  it('validateRoute fails without station ids', () => {
    const { store } = createWizardStore(minimalWizardConfig());
    expect(store.validateRoute()).toBe(false);
    expect(store.error.length).toBeGreaterThan(0);
  });

  it('setRoute resets trip selections', () => {
    const { store } = createWizardStore(minimalWizardConfig());
    store.dateYmd = '2026-05-01';
    store.setRoute(1, 2, 'single', 'A', 'B');
    expect(store.fromId).toBe(1);
    expect(store.dateYmd).toBe('');
  });
});
