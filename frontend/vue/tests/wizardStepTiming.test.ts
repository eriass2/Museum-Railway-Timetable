import { describe, expect, it, vi } from 'vitest';
import { logWizardStepInteractive } from '../src/wizard/utils/wizardStepTiming';
import { configureMrtLog, resetMrtLogForTests } from '../src/utils/mrtLog';

describe('logWizardStepInteractive', () => {
  it('logs step timing in dev mode', () => {
    resetMrtLogForTests();
    configureMrtLog({ isDevMode: true });
    const infoSpy = vi.spyOn(console, 'info').mockImplementation(() => undefined);

    logWizardStepInteractive({ isDevMode: true, app: 'wizard' }, 'date', 42.7);

    expect(infoSpy).toHaveBeenCalledWith(
      '[MRT wizard]',
      'wizard step interactive: date 43ms',
      { step: 'date', durationMs: 43 },
    );
  });

  it('is silent outside dev mode', () => {
    resetMrtLogForTests();
    const infoSpy = vi.spyOn(console, 'info').mockImplementation(() => undefined);

    logWizardStepInteractive({ isDevMode: false }, 'outbound', 10);

    expect(infoSpy).not.toHaveBeenCalled();
  });
});
