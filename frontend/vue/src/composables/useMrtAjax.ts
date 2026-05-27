import { ref } from 'vue';
import { mrtPost, type MrtAjaxResponse } from '../api/mrtApi';
import type { MrtAjaxConfig } from '../config/types';

export function useMrtAjax(config: MrtAjaxConfig) {
  const loading = ref(false);
  const error = ref('');

  async function run<T>(
    action: string,
    data: Record<string, string | number> = {},
  ): Promise<MrtAjaxResponse<T>> {
    loading.value = true;
    error.value = '';
    const res = await mrtPost<T>(config, action, data);
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
