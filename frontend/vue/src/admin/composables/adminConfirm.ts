import { ref, shallowRef } from 'vue';
import { adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

export type AdminConfirmOptions = {
  title: string;
  message: string;
  confirmLabel?: string;
  cancelLabel?: string;
  danger?: boolean;
};

const open = ref(false);
const options = shallowRef<AdminConfirmOptions | null>(null);
let resolvePending: ((value: boolean) => void) | null = null;

export function adminConfirm(opts: AdminConfirmOptions): Promise<boolean> {
  const cfg = adminConfig();
  options.value = {
    confirmLabel: adminStr(cfg, 'confirm', 'Bekräfta'),
    cancelLabel: adminStr(cfg, 'cancel', 'Avbryt'),
    ...opts,
  };
  open.value = true;
  return new Promise((resolve) => {
    resolvePending = resolve;
  });
}

export function useAdminConfirmDialog() {
  function close(result: boolean) {
    open.value = false;
    options.value = null;
    resolvePending?.(result);
    resolvePending = null;
  }

  return {
    open,
    options,
    confirm: () => close(true),
    cancel: () => close(false),
  };
}
