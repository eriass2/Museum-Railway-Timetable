import { computed, ref, type Ref } from 'vue';

export function useAdminFormDirty<T>(source: Ref<T | null>) {
  const savedSnapshot = ref('');

  function serialize(value: T): string {
    return JSON.stringify(value);
  }

  function syncSnapshot() {
    if (source.value !== null) {
      savedSnapshot.value = serialize(source.value);
    }
  }

  const dirty = computed(() => {
    if (source.value === null || savedSnapshot.value === '') {
      return false;
    }
    return serialize(source.value) !== savedSnapshot.value;
  });

  return { dirty, syncSnapshot };
}
