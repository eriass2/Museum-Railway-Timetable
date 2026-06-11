import { ref } from 'vue';
import {
  devClearDatabase,
  devCreateDemoPage,
  devImportLennakatten,
  devSetupNavigation,
  devSyncTimetablePages,
} from '../api/adminRest';
import { adminConfirm } from './adminConfirm';
import { adminConfig } from '../types';
import { adminErrorMessage, adminStr } from '../utils/adminLabels';

const DEV_ACTION_LABELS = [
  'clear',
  'import',
  'demo',
  'nav',
  'pages',
] as const;

type DevAction = (typeof DEV_ACTION_LABELS)[number];

function isDevAction(action: string): action is DevAction {
  return (DEV_ACTION_LABELS as readonly string[]).includes(action);
}

export function useDevToolsPage() {
  const cfg = adminConfig();
  const busy = ref('');
  const message = ref('');
  const error = ref('');

  async function confirmClearIfNeeded(action: string): Promise<boolean> {
    if (action !== 'clear') {
      return true;
    }
    return adminConfirm({
      title: adminStr(cfg, 'devClearTitle'),
      message: adminStr(cfg, 'devClearMessage'),
      confirmLabel: adminStr(cfg, 'devClearConfirm'),
      danger: true,
    });
  }

  function successMessage(action: DevAction): string {
    const labels: Record<DevAction, string> = {
      clear: adminStr(cfg, 'devClearSuccess'),
      import: adminStr(cfg, 'devImportSuccess'),
      demo: adminStr(cfg, 'devDemoSuccess'),
      nav: adminStr(cfg, 'devNavSuccess'),
      pages: adminStr(cfg, 'devPagesSuccess'),
    };
    return labels[action];
  }

  async function run(action: string, fn: () => Promise<unknown>) {
    if (busy.value) {
      return;
    }
    if (!(await confirmClearIfNeeded(action))) {
      return;
    }
    busy.value = action;
    error.value = '';
    message.value = '';
    try {
      await fn();
      message.value = isDevAction(action) ? successMessage(action) : adminStr(cfg, 'devDone');
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'genericError');
    } finally {
      busy.value = '';
    }
  }

  return {
    cfg,
    busy,
    message,
    error,
    run,
    devClearDatabase,
    devImportLennakatten,
    devCreateDemoPage,
    devSetupNavigation,
    devSyncTimetablePages,
  };
}
