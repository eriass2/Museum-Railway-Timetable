import { computed, toValue, type MaybeRefOrGetter } from 'vue';
import type { JourneyConnection } from '../types';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStr } from '../utils/wizardLabels';
import {
  connectionDoorToDoorMinutes,
  connectionLegs,
  connectionTransferCount,
  formatTransferTripLabel,
  isTransfer,
} from '../utils/connection';
import { connectionNoticeIsCancelled } from '../utils/format';
import { legsToVehicleItems } from '../utils/vehicle';

export function useTripCardDisplay(
  connection: MaybeRefOrGetter<JourneyConnection>,
  cfg: MaybeRefOrGetter<WizardCfg>,
) {
  const meta = computed(() => {
    const conn = toValue(connection);
    const cfgVal = toValue(cfg);
    if (!isTransfer(conn)) {
      return cfgStr(cfgVal, 'directTrip', 'Direktresa');
    }
    return formatTransferTripLabel(connectionTransferCount(conn), cfgVal);
  });

  const legs = computed(() => connectionLegs(toValue(connection)));

  const doorToDoorMinutes = computed(() =>
    connectionDoorToDoorMinutes(toValue(connection)),
  );

  const vehicleItems = computed(() =>
    legsToVehicleItems(legs.value, toValue(cfg)),
  );

  const isCancelled = computed(() =>
    connectionNoticeIsCancelled(toValue(connection)),
  );

  return { meta, legs, doorToDoorMinutes, vehicleItems, isCancelled };
}
