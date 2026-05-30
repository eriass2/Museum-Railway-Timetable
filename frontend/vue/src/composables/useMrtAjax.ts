import { ref } from 'vue';
import { mrtRestRequest, type MrtAjaxResponse } from '../api/mrtRest';
import type { MrtRestConfig } from '../config/types';

export function useMrtAjax(config: MrtRestConfig) {
  const loading = ref(false);
  const error = ref('');

  async function run<T>(
    action: string,
    data: Record<string, string | number> = {},
  ): Promise<MrtAjaxResponse<T>> {
    loading.value = true;
    error.value = '';
    const res = await mrtRestRequest<T>(config, action, data);
    loading.value = false;
    if (!res.success) {
      error.value = res.message || 'Begäran misslyckades';
    }
    return res;
  }

  function clearError(): void {
    error.value = '';
  }

  return { loading, error, run, clearError };
}
