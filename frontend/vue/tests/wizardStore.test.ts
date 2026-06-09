import { describe, expect, it } from 'vitest';
import { createWizardStore } from '../src/wizard/store/createWizardStore';
import type { WizardVueConfig } from '../src/config/types';
import {
  wizardContextLine,
  wizardStepLabels,
} from '../src/wizard/store/wizardStoreGetters';
import {
  applyInboundSelection,
  applyOutboundSelection,
} from '../src/wizard/store/wizardSelections';
import { navigateToCompletedWizardStep } from '../src/wizard/store/wizardRoute';
import type { JourneyConnection } from '../src/wizard/types';

function wizardConfig(): WizardVueConfig {
  return {
    app: 'wizard',
    stations: [],
    wizard: {
      tripSingle: 'Enkel resa',
      tripReturn: 'Tur och retur',
      stepRoute: 'Sök',
      stepDate: 'Datum',
      stepOutbound: 'Utresa',
      stepReturn: 'Retur',
      stepSummary: 'Sammanfattning',
      monthNames: ['januari', 'februari', 'mars', 'april', 'maj', 'juni'],
    },
    labels: {},
  };
}

describe('wizardStoreGetters', () => {
  it('wizardContextLine includes route and date', () => {
    const line = wizardContextLine(
      {
        tripType: 'single',
        fromTitle: 'Alpha',
        toTitle: 'Beta',
        dateYmd: '2026-06-04',
      },
      {
        tripSingle: 'Enkel resa',
        tripReturn: 'Tur och retur',
        monthNames: ['januari', 'februari', 'mars', 'april', 'maj', 'juni'],
      },
    );

    expect(line).toContain('Alpha → Beta');
    expect(line).toContain('Enkel resa');
    expect(line).toContain('juni');
  });

  it('wizardStepLabels maps all steps', () => {
    const { store, cfg } = createWizardStore(wizardConfig());
    const labels = wizardStepLabels(cfg.value);

    expect(labels.route).toBe('Sök');
    expect(store.stepLabels.route).toBe('Sök');
  });
});

describe('wizardSelections', () => {
  it('applyOutboundSelection advances to summary for single trip', () => {
    const { store } = createWizardStore(wizardConfig());
    const conn: JourneyConnection = { service_id: 1, from_departure: '09:00', to_arrival: '10:00' };

    applyOutboundSelection(store, conn);

    expect(store.outbound).toEqual(conn);
    expect(store.inbound).toBeNull();
    expect(store.step).toBe('summary');
  });

  it('applyOutboundSelection advances to return for round trip', () => {
    const { store } = createWizardStore(wizardConfig());
    store.tripType = 'return';
    const conn: JourneyConnection = { service_id: 1, from_departure: '09:00', to_arrival: '10:00' };

    applyOutboundSelection(store, conn);

    expect(store.step).toBe('return');
  });

  it('applyInboundSelection stores inbound and opens summary', () => {
    const { store } = createWizardStore(wizardConfig());
    store.tripType = 'return';
    const conn: JourneyConnection = { service_id: 2, from_departure: '14:00', to_arrival: '15:00' };

    applyInboundSelection(store, conn);

    expect(store.inbound).toEqual(conn);
    expect(store.step).toBe('summary');
  });
});

describe('navigateToCompletedWizardStep', () => {
  const outbound: JourneyConnection = { service_id: 1, from_departure: '09:00', to_arrival: '10:00' };
  const inbound: JourneyConnection = { service_id: 2, from_departure: '14:00', to_arrival: '15:00' };

  it('ignores future and current steps', () => {
    const { store } = createWizardStore(wizardConfig());
    store.step = 'date';

    expect(navigateToCompletedWizardStep(store, 'date')).toBe(false);
    expect(navigateToCompletedWizardStep(store, 'outbound')).toBe(false);
    expect(store.step).toBe('date');
  });

  it('jumps to route and clears downstream selections', () => {
    const { store } = createWizardStore(wizardConfig());
    store.step = 'summary';
    store.dateYmd = '2026-06-04';
    store.outbound = outbound;

    expect(navigateToCompletedWizardStep(store, 'route')).toBe(true);

    expect(store.step).toBe('route');
    expect(store.dateYmd).toBe('');
    expect(store.outbound).toBeNull();
    expect(store.inbound).toBeNull();
  });

  it('jumps to date and keeps the selected day', () => {
    const { store } = createWizardStore(wizardConfig());
    store.step = 'summary';
    store.dateYmd = '2026-06-04';
    store.outbound = outbound;

    expect(navigateToCompletedWizardStep(store, 'date')).toBe(true);

    expect(store.step).toBe('date');
    expect(store.dateYmd).toBe('2026-06-04');
    expect(store.outbound).toBeNull();
  });

  it('jumps to return and clears inbound only', () => {
    const { store } = createWizardStore(wizardConfig());
    store.tripType = 'return';
    store.step = 'summary';
    store.dateYmd = '2026-06-04';
    store.outbound = outbound;
    store.inbound = inbound;

    expect(navigateToCompletedWizardStep(store, 'return')).toBe(true);

    expect(store.step).toBe('return');
    expect(store.outbound).toEqual(outbound);
    expect(store.inbound).toBeNull();
  });
});
