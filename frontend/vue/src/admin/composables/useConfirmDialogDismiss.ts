export function useConfirmDialogDismiss(cancel: () => void) {
  function onBackdropClick(event: MouseEvent) {
    if (event.target === event.currentTarget) {
      cancel();
    }
  }

  function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
      cancel();
    }
  }

  return { onBackdropClick, onKeydown };
}
