import { computed, type Ref } from 'vue';

export function useTripFieldIds(fieldIdPrefix: Ref<string>) {
  const lineSelectId = computed(() => `${fieldIdPrefix.value}-line`);
  const directionSelectId = computed(() => `${fieldIdPrefix.value}-direction`);
  const serviceNumberInputId = computed(() => `${fieldIdPrefix.value}-num`);
  const trainTypeSelectId = computed(() => `${fieldIdPrefix.value}-type`);

  return { lineSelectId, directionSelectId, serviceNumberInputId, trainTypeSelectId };
}
