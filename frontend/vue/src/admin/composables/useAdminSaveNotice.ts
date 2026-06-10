import { onUnmounted, ref, watch } from 'vue';

const NOTICE_MS = 5000;

export function useAdminSaveNotice() {
  const saveMsg = ref('');
  let timer: ReturnType<typeof setTimeout> | null = null;

  function clearTimer() {
    if (timer) {
      clearTimeout(timer);
      timer = null;
    }
  }

  function show(message: string) {
    saveMsg.value = message;
    clearTimer();
    timer = setTimeout(() => {
      saveMsg.value = '';
      timer = null;
    }, NOTICE_MS);
  }

  watch(saveMsg, (msg) => {
    if (!msg) {
      clearTimer();
    }
  });

  onUnmounted(clearTimer);

  return { saveMsg, show };
}
