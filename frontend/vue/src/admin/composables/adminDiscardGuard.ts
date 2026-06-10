import { adminConfirm } from './adminConfirm';
import { adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

export async function confirmDiscardChanges(): Promise<boolean> {
  const cfg = adminConfig();
  return adminConfirm({
    title: adminStr(cfg, 'discardChangesTitle'),
    message: adminStr(cfg, 'discardChangesMessage'),
    confirmLabel: adminStr(cfg, 'discardChangesConfirm'),
    danger: true,
  });
}

export async function proceedIfDiscardAllowed(isDirty: boolean): Promise<boolean> {
  if (!isDirty) {
    return true;
  }
  return confirmDiscardChanges();
}
