import { ref } from 'vue';
import {
  mrtRestRequest,
  type MrtRestRequestInit,
  type MrtRestResponse,
} from '../api/mrtRest';
import type { MrtRestConfig } from '../config/types';
import { resolveMrtString } from '../utils/mrtStrings';

export function useMrtRest(config: MrtRestConfig) {
  const loading = ref(false);
  const error = ref('');

  async function run<T>(init: MrtRestRequestInit): Promise<MrtRestResponse<T>> {
    loading.value = true;
    error.value = '';
    const res = await mrtRestRequest<T>(config, init);
    loading.value = false;
    if (!res.success) {
      error.value =
        res.message || resolveMrtString(config, 'requestFailed', 'Begäran misslyckades.');
    }
    return res;
  }

  function clearError(): void {
    error.value = '';
  }

  return { loading, error, run, clearError };
}
