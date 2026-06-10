import { describe, expect, it } from 'vitest';
import { cfgStr, wizardCfg } from '../src/wizard/utils/wizardLabels';
import type { WizardVueConfig } from '../src/config/types';

describe('cfgStr', () => {
  it('reads merged wizard config via resolveMrtString', () => {
    const config = {
      app: 'wizard',
      wizard: { stepRoute: 'Från wizard' },
      labels: { stepRoute: 'Från labels' },
    } as WizardVueConfig;

    expect(cfgStr(wizardCfg(config), 'stepRoute', 'fallback')).toBe('Från labels');
  });

  it('returns fallback for missing or empty strings', () => {
    const config = { app: 'wizard', wizard: { stepRoute: '' } } as WizardVueConfig;
    expect(cfgStr(wizardCfg(config), 'stepRoute', 'fallback')).toBe('fallback');
  });
});
