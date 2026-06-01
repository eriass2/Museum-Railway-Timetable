import { ref } from 'vue';

export function useAdminRowFlash(durationMs = 1600) {
  const flashedRowId = ref<number | null>(null);
  let timer: ReturnType<typeof setTimeout> | null = null;

  function flashRow(id: number) {
    flashedRowId.value = id;
    if (timer) {
      clearTimeout(timer);
    }
    timer = setTimeout(() => {
      if (flashedRowId.value === id) {
        flashedRowId.value = null;
      }
      timer = null;
    }, durationMs);
  }

  function isFlashed(id: number): boolean {
    return flashedRowId.value === id;
  }

  return { flashedRowId, flashRow, isFlashed };
}
