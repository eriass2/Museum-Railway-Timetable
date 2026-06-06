import { onBeforeUnmount, onMounted, type Ref } from 'vue';
import { onBeforeRouteLeave } from 'vue-router';
import { proceedIfDiscardAllowed } from './adminDiscardGuard';

/**
 * Warn when leaving the page or route with unsaved admin form changes.
 */
export function useAdminUnsavedGuard(dirty: Ref<boolean>): void {
  function onBeforeUnload(event: BeforeUnloadEvent) {
    if (!dirty.value) {
      return;
    }
    event.preventDefault();
    event.returnValue = '';
  }

  onBeforeRouteLeave(async () => proceedIfDiscardAllowed(dirty.value));

  onMounted(() => {
    window.addEventListener('beforeunload', onBeforeUnload);
  });

  onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', onBeforeUnload);
  });
}
