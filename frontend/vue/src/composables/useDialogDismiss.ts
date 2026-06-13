import { nextTick, onUnmounted, watch, type Ref } from 'vue';

const FOCUSABLE =
  'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

export function useDialogDismiss(close: () => void) {
  function onBackdropClick(event: MouseEvent): void {
    if (event.target === event.currentTarget) {
      close();
    }
  }

  function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
      close();
    }
  }

  return { onBackdropClick, onKeydown };
}

export function useFocusTrap(container: Ref<HTMLElement | null>, active: Ref<boolean>): void {
  let previousFocus: HTMLElement | null = null;

  function focusables(): HTMLElement[] {
    if (!container.value) {
      return [];
    }
    return Array.from(container.value.querySelectorAll<HTMLElement>(FOCUSABLE)).filter(
      (el) => !el.closest('[aria-hidden="true"]'),
    );
  }

  function onTrapKeydown(event: KeyboardEvent): void {
    if (event.key !== 'Tab' || !container.value) {
      return;
    }
    const items = focusables();
    if (items.length === 0) {
      return;
    }
    const first = items[0];
    const last = items[items.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
      return;
    }
    if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }

  watch(active, (isActive) => {
    if (isActive) {
      previousFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
      void nextTick(() => {
        focusables()[0]?.focus();
        container.value?.addEventListener('keydown', onTrapKeydown);
      });
      return;
    }
    container.value?.removeEventListener('keydown', onTrapKeydown);
    previousFocus?.focus();
    previousFocus = null;
  });

  onUnmounted(() => {
    container.value?.removeEventListener('keydown', onTrapKeydown);
  });
}
