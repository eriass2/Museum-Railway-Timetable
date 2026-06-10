import { onMounted, ref, type Ref } from 'vue';

export type UseAdminResourceOptions<T> = {
  fetch: () => Promise<T>;
  errorMessage?: (error: unknown) => string;
  /** Return false to skip fetch (sets deniedMessage on error if provided). */
  beforeLoad?: () => boolean;
  deniedMessage?: string;
  immediate?: boolean;
};

export function useAdminResource<T>(options: UseAdminResourceOptions<T>) {
  const loading = ref(options.immediate !== false);
  const error = ref('');
  const data = ref(undefined) as Ref<T | undefined>;

  async function load() {
    if (options.beforeLoad && !options.beforeLoad()) {
      if (options.deniedMessage) {
        error.value = options.deniedMessage;
      }
      loading.value = false;
      return;
    }
    loading.value = true;
    error.value = '';
    try {
      data.value = await options.fetch();
    } catch (e) {
      data.value = undefined;
      error.value =
        options.errorMessage?.(e) ?? (e instanceof Error ? e.message : String(e));
    } finally {
      loading.value = false;
    }
  }

  if (options.immediate !== false) {
    onMounted(() => {
      void load();
    });
  }

  return { loading, error, data, load, reload: load };
}
