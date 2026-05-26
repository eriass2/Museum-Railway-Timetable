import $ from 'jquery';

type WizardCfg = Record<string, unknown> & {
  ajaxurl?: string;
  nonce?: string;
  debug?: string;
  startOfWeek?: number;
};

declare global {
  interface Window {
    MRTJourneyWizard: {
      runtime: { createRuntime: (root: JQuery, cfg: WizardCfg, startOfWeek: number) => WizardContext };
      events: { bindAll: (ctx: WizardContext) => void };
      debug?: { applyDebugPreset: (ctx: WizardContext, root: JQuery, cfg: WizardCfg) => void };
    };
    mrtJourneyWizard: WizardCfg;
    mrtFrontend: Record<string, string>;
  }
}

type WizardContext = {
  buildStepNav: () => void;
  updateStepNav: (step: string) => void;
};

window.jQuery = $;
window.$ = $;

import '../lib/mrt-string-utils.js';
import '../lib/mrt-date-utils.js';
import '../lib/mrt-frontend-api.js';
import './legacy/namespace.js';
import './legacy/constants.js';
import './legacy/render.js';
import './legacy/connection.js';
import './legacy/context.js';
import './legacy/prices.js';
import './legacy/vehicle.js';
import './legacy/calendar.js';
import './legacy/trip-card.js';
import './legacy/connection-detail.js';
import './legacy/summary.js';
import './legacy/runtime.js';
import './legacy/events.js';
import './legacy/debug.js';

export function mountJourneyWizard(root: HTMLElement, cfg: WizardCfg): void {
  const wizardCfg: WizardCfg = {
    ...((cfg.wizard as WizardCfg) || {}),
    ajaxurl: cfg.ajaxurl,
    nonce: cfg.nonce,
    startOfWeek: cfg.startOfWeek,
  };
  window.mrtJourneyWizard = wizardCfg;
  window.mrtFrontend = {
    ...((cfg.strings as Record<string, string>) || {}),
    ajaxurl: String(cfg.ajaxurl || ''),
    nonce: String(cfg.nonce || ''),
  };

  const startOfWeek = Number(cfg.startOfWeek);
  const sow = Number.isFinite(startOfWeek) && startOfWeek >= 0 && startOfWeek <= 6 ? startOfWeek : 1;
  const $root = $(root);
  const wctx = window.MRTJourneyWizard.runtime.createRuntime($root, wizardCfg, sow);
  window.MRTJourneyWizard.events.bindAll(wctx);
  wctx.buildStepNav();

  const debugKey = String(cfg.debug || '');
  if (debugKey && window.MRTJourneyWizard.debug?.applyDebugPreset) {
    window.MRTJourneyWizard.debug.applyDebugPreset(wctx, $root, wizardCfg);
  } else {
    wctx.updateStepNav('route');
  }
}
