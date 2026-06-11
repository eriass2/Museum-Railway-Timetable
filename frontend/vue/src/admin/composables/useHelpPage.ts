import { computed } from 'vue';
import type { AdminHelpContent } from '../types';
import { adminConfig } from '../types';
import { requireAdminHelp } from '../utils/adminHelpContent';

export function useHelpPage() {
  const cfg = adminConfig();
  const help = computed((): AdminHelpContent => requireAdminHelp(cfg));

  const visibleAdminSections = computed(() =>
    help.value.adminSections.filter((section) => {
      if (section.adminOnly && !cfg.canManage) {
        return false;
      }
      if (section.devOnly && !cfg.isDevMode) {
        return false;
      }
      return true;
    }),
  );

  function faqAnswer(item: (typeof help.value.faq)[number]): string {
    if (item.aEditor && !cfg.canManage) {
      return item.aEditor;
    }
    return item.a;
  }

  return {
    cfg,
    help,
    visibleAdminSections,
    faqAnswer,
  };
}
