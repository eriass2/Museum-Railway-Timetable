import { ref, watch, type ComponentPublicInstance, type Ref } from 'vue';
import { useRoute } from 'vue-router';
import type { TripsPanelView } from '../../components/timetable-editor/TimetableEditorTripsTab.vue';
import {
  parseTimetableEditorTab,
  type StoptimesPanelView,
  type TimetableEditorTab,
} from './timetableEditorTypes';

export function useTimetableEditorTabNavigation(options: {
  tripsView: Ref<TripsPanelView>;
  requestBackToTripsList: () => Promise<boolean>;
  stoptimesView: Ref<StoptimesPanelView>;
  requestBackToStoptimesList: () => Promise<boolean>;
  deviationsTabRef: Ref<ComponentPublicInstance<{ requestBackToList: () => Promise<boolean> }> | null>;
  onTabActivated: (tab: TimetableEditorTab) => void | Promise<void>;
}) {
  const route = useRoute();
  const tab = ref<TimetableEditorTab>('dates');

  async function leaveActiveSubView(): Promise<boolean> {
    if (options.tripsView.value !== 'list' && tab.value === 'trips') {
      return options.requestBackToTripsList();
    }
    if (options.stoptimesView.value === 'detail' && tab.value === 'stoptimes') {
      return options.requestBackToStoptimesList();
    }
    if (tab.value === 'deviations') {
      return (await options.deviationsTabRef.value?.requestBackToList()) ?? true;
    }
    return true;
  }

  async function switchTab(next: TimetableEditorTab): Promise<void> {
    if (tab.value === next) {
      return;
    }
    if (!(await leaveActiveSubView())) {
      return;
    }
    tab.value = next;
  }

  function initRouteTabSync(): void {
    const initialTab = parseTimetableEditorTab(route.query.tab);
    if (initialTab) {
      tab.value = initialTab;
    }
    watch(
      () => route.query.tab,
      (nextTab) => {
        const parsed = parseTimetableEditorTab(nextTab);
        if (parsed) {
          tab.value = parsed;
        }
      },
    );
  }

  watch(tab, (nextTab) => {
    void options.onTabActivated(nextTab);
  });

  return {
    tab,
    switchTab,
    leaveActiveSubView,
    initRouteTabSync,
  };
}
