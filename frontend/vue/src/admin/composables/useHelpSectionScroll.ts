import { nextTick, onMounted, watch } from 'vue';
import type { RouteLocationNormalizedLoaded } from 'vue-router';

function sectionIdFromQuery(section: unknown): string | undefined {
  return typeof section === 'string' ? section : undefined;
}

function scrollToHelpSection(sectionId: string | undefined): void {
  if (!sectionId) {
    return;
  }
  void nextTick(() => {
    document.getElementById(`mrt-help-${sectionId}`)?.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    });
  });
}

/** Deep-link scroll for HelpPage section anchors from route query. */
export function useHelpSectionScroll(route: RouteLocationNormalizedLoaded): {
  scrollToHelpSection: (sectionId: string | undefined) => void;
} {
  onMounted(() => {
    scrollToHelpSection(sectionIdFromQuery(route.query.section));
  });

  watch(
    () => route.query.section,
    (section) => {
      scrollToHelpSection(sectionIdFromQuery(section));
    },
  );

  return { scrollToHelpSection };
}
