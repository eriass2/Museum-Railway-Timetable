import type { MaybeRef } from 'vue';
import { unref } from 'vue';
import { resolveMrtString } from '../../utils/mrtStrings';
import type { AdminClientConfig } from '../types';

/** Keys supplied by {@link MRT_admin_vue_script_localization} in PHP. */
export type AdminStringKey =
  | 'saved'
  | 'loading'
  | 'retry'
  | 'loadFailed'
  | 'saveFailed'
  | 'navBrand'
  | 'navAria'
  | 'navOverview'
  | 'navStationsRoutes'
  | 'navTimetables'
  | 'navHelp'
  | 'navTrainTypes'
  | 'navSettings'
  | 'navPrices'
  | 'navImportExport'
  | 'navDev'
  | 'navComponentDemo'
  | 'settingsTitle'
  | 'settingsLoading'
  | 'settingsNoPermission'
  | 'settingsLoadFailed'
  | 'settingsSaveButton'
  | 'settingsEnabledLabel'
  | 'settingsEnabledCheckbox'
  | 'settingsNote'
  | 'settingsMinTransfer'
  | 'settingsMaxTransfer'
  | 'settingsImportHint'
  | 'pricesTitle'
  | 'pricesLoading'
  | 'pricesNoPermission'
  | 'pricesLoadFailed'
  | 'pricesSaveButton'
  | 'pricesDescription'
  | 'pricesTicketTypeCol'
  | 'pricesZonesCol';

export function adminStr(
  cfg: MaybeRef<AdminClientConfig>,
  key: AdminStringKey,
  fallback = '',
): string {
  return resolveMrtString({ strings: unref(cfg).strings }, key, fallback);
}
