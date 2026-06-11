import { nextTick, watch, type Ref } from 'vue';
import type { WizardVueConfig } from '../../config/types';
import type { WizardStep } from '../types';
import { logWizardStepInteractive } from '../utils/wizardStepTiming';

function focusActiveStepPanel(panelsRef: Ref<HTMLElement | null>): void {
  const panel = panelsRef.value?.querySelector('.mrt-step-panel--active');
  const focusEl = panel?.querySelector(
    '.mrt-step-progress__item.is-active, h2.mrt-heading--surface-title',
  ) as HTMLElement | null;
  if (!focusEl) {
    return;
  }
  focusEl.setAttribute('tabindex', '-1');
  focusEl.focus();
  focusEl.addEventListener('blur', () => focusEl.removeAttribute('tabindex'), { once: true });
}

export function useWizardStepFocus(
  config: WizardVueConfig,
  step: () => WizardStep,
  panelsRef: Ref<HTMLElement | null>,
): void {
  watch(step, async (nextStep) => {
    const started = performance.now();
    await nextTick();
    const panel = panelsRef.value?.querySelector('.mrt-step-panel--active');
    if (panel) {
      logWizardStepInteractive(config, nextStep, performance.now() - started);
    }
    focusActiveStepPanel(panelsRef);
  });
}
