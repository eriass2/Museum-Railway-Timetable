import { computed, ref, watch, type Ref } from 'vue';

/** Nested expand state for a disruption feed row (parent owns summary expanded). */
export function useDisruptionFeedItemDetails(
  expanded: Ref<boolean>,
  hasIntro: Ref<boolean>,
) {
  const detailsOpen = ref(false);

  const showSectionsPanel = computed(
    () => detailsOpen.value || (expanded.value && !hasIntro.value),
  );

  watch(expanded, (open) => {
    if (open && !hasIntro.value) {
      detailsOpen.value = true;
      return;
    }
    if (!open) {
      detailsOpen.value = false;
    }
  });

  function onSummaryToggle(willOpen: boolean): void {
    if (willOpen && !hasIntro.value) {
      detailsOpen.value = true;
    }
    if (!willOpen) {
      detailsOpen.value = false;
    }
  }

  return { detailsOpen, showSectionsPanel, onSummaryToggle };
}
