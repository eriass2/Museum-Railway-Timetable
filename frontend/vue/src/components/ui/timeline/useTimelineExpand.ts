import { ref } from 'vue';

export function useTimelineExpand(startExpanded: boolean) {
  const showAllStops = ref(startExpanded);

  function toggleStops(): void {
    showAllStops.value = !showAllStops.value;
  }

  return { showAllStops, toggleStops };
}
