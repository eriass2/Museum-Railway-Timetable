import { onMounted, onUnmounted, ref } from 'vue';

const MOBILE_QUERY = '(max-width: 782px)';

export function useMobileAdmin() {
  const isMobile = ref(false);
  let media: MediaQueryList | null = null;

  function sync(event?: MediaQueryListEvent) {
    isMobile.value = event ? event.matches : (media?.matches ?? false);
  }

  onMounted(() => {
    media = window.matchMedia(MOBILE_QUERY);
    sync();
    media.addEventListener('change', sync);
  });

  onUnmounted(() => {
    media?.removeEventListener('change', sync);
  });

  return { isMobile };
}
