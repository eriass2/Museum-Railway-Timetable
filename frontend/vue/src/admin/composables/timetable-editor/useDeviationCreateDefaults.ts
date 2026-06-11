import { ref, watch, type Ref } from 'vue';
import type { TimetableDetail } from '../../types';

export function useDeviationCreateDefaults(
  dates: Ref<string[]>,
  services: Ref<TimetableDetail['services']>,
) {
  const newDate = ref('');
  const newServiceId = ref(0);

  watch(
    () => [dates.value, services.value] as const,
    ([nextDates, nextServices]) => {
      if (!newDate.value && nextDates.length) {
        newDate.value = nextDates[0];
      }
      if (!newServiceId.value && nextServices.length) {
        newServiceId.value = nextServices[0].id;
      }
    },
    { immediate: true },
  );

  return { newDate, newServiceId };
}
