import type { Ref } from 'vue';
import { adminErrorMessage } from '../utils/adminLabels';
import { adminConfig } from '../types';

/** Wrap admin REST mutations with consistent user-visible errors. */
export function useAdminMutation(error: Ref<string>) {
  const cfg = adminConfig();

  async function runMutation(fn: () => Promise<unknown>, errorKey = 'saveFailed'): Promise<boolean> {
    error.value = '';
    try {
      await fn();
      return true;
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, errorKey);
      return false;
    }
  }

  async function runMutationWithResult<T>(
    fn: () => Promise<T>,
    errorKey = 'saveFailed',
  ): Promise<T | null> {
    error.value = '';
    try {
      return await fn();
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, errorKey);
      return null;
    }
  }

  return { runMutation, runMutationWithResult };
}
