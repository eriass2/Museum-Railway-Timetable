import { computed, ref } from 'vue';
import { getPrices, getSettings, savePrices, saveSettings } from '../../api/adminRest';
import type { PricesPayload, SettingsPayload } from '../../api/adminRest';
import { usePriceSchemaEditor } from './usePriceSchemaEditor';
import { useAdminResource } from '../useAdminResource';
import { useAdminSaveNotice } from '../useAdminSaveNotice';
import { useAdminFormDirty } from '../useAdminFormDirty';
import { useAdminUnsavedGuard } from '../useAdminUnsavedGuard';
import { adminConfirm } from '../adminConfirm';
import { adminErrorMessage, adminFmtN, adminStr } from '../../utils/adminLabels';
import {
  hasMatrixZonesBeyondCap,
  priceMatrixHasAnyValue,
} from '../../utils/prices/adminPricePreview';
import { adminConfig } from '../../types';

export function usePricesPage() {
  const cfg = adminConfig();
  const { saveMsg, show: showSaved } = useAdminSaveNotice();
  const data = ref<PricesPayload | null>(null);
  const settingsSnapshot = ref<SettingsPayload | null>(null);
  const afternoonThresholdMinutes = ref(900);
  const savedThresholdMinutes = ref(900);
  const newTicketLabel = ref('');
  const newCategoryLabel = ref('');
  const newZone = ref('');
  const copyZoneFrom = ref<number | ''>('');
  const copyZoneTo = ref<number | ''>('');

  const { dirty: pricesDirty, syncSnapshot } = useAdminFormDirty(data);
  const thresholdDirty = computed(
    () => afternoonThresholdMinutes.value !== savedThresholdMinutes.value,
  );
  const dirty = computed(() => pricesDirty.value || thresholdDirty.value);
  useAdminUnsavedGuard(dirty);

  function syncAllSnapshots() {
    syncSnapshot();
    savedThresholdMinutes.value = afternoonThresholdMinutes.value;
  }

  const {
    addTicketType,
    removeTicketType,
    addCategory,
    removeCategory,
    addZone,
    removeZone,
    copyZonePrices,
    ensureMatrixCells,
  } = usePriceSchemaEditor(data);

  const { loading, error, load } = useAdminResource({
    beforeLoad: () => cfg.canManage,
    deniedMessage: adminStr(cfg, 'pricesNoPermission'),
    fetch: async () => {
      const [payload, settings] = await Promise.all([getPrices(), getSettings()]);
      settingsSnapshot.value = { ...settings };
      afternoonThresholdMinutes.value = settings.afternoon_return_threshold_minutes;
      savedThresholdMinutes.value = settings.afternoon_return_threshold_minutes;
      if (payload.zone_cap === undefined) {
        payload.zone_cap = 3;
      }
      if (!payload.afternoon_return) {
        payload.afternoon_return = {};
      }
      if (!payload.ticket_copy_notes) {
        payload.ticket_copy_notes = [];
      }
      data.value = payload;
      ensureMatrixCells(payload);
      syncAllSnapshots();
      return payload;
    },
    errorMessage: (e) => {
      data.value = null;
      return adminErrorMessage(cfg, e, 'pricesLoadFailed');
    },
  });

  const ticketKeys = computed(() => Object.keys(data.value?.ticket_types ?? {}));
  const categoryKeys = computed(() => Object.keys(data.value?.categories ?? {}));
  const zones = computed(() => data.value?.zones ?? []);
  const matrixConfigured = computed(() => (data.value ? priceMatrixHasAnyValue(data.value) : false));
  const zonesBeyondCap = computed(() =>
    data.value ? hasMatrixZonesBeyondCap(data.value) : false,
  );

  function cellValue(ticket: string, category: string, zone: number): number | '' {
    const v = data.value?.matrix[ticket]?.[category]?.[zone];
    return v === null || v === undefined ? '' : v;
  }

  function cellIsEmpty(ticket: string, category: string, zone: number): boolean {
    return cellValue(ticket, category, zone) === '';
  }

  function setCell(ticket: string, category: string, zone: number, raw: string) {
    if (!data.value) {
      return;
    }
    if (!data.value.matrix[ticket]) {
      data.value.matrix[ticket] = {};
    }
    if (!data.value.matrix[ticket][category]) {
      data.value.matrix[ticket][category] = {};
    }
    data.value.matrix[ticket][category][zone] = raw === '' ? null : Number(raw);
  }

  function submitAddTicketType() {
    addTicketType(newTicketLabel.value);
    newTicketLabel.value = '';
  }

  function submitAddCategory() {
    addCategory(newCategoryLabel.value);
    newCategoryLabel.value = '';
  }

  function submitAddZone() {
    const zone = parseInt(newZone.value, 10);
    if (!Number.isFinite(zone)) {
      return;
    }
    addZone(zone);
    newZone.value = '';
  }

  function submitCopyZone() {
    if (copyZoneFrom.value === '' || copyZoneTo.value === '') {
      return;
    }
    copyZonePrices(Number(copyZoneFrom.value), Number(copyZoneTo.value));
  }

  async function confirmRemoveTicketType(key: string) {
    if (!data.value) {
      return;
    }
    const ok = await adminConfirm({
      title: adminStr(cfg, 'pricesDeleteTicketTitle'),
      message: adminFmtN(cfg, 'pricesDeleteTicketMsg', { 1: data.value.ticket_types[key] ?? key }),
      danger: true,
    });
    if (ok) {
      removeTicketType(key);
    }
  }

  async function confirmRemoveCategory(key: string) {
    if (!data.value) {
      return;
    }
    const ok = await adminConfirm({
      title: adminStr(cfg, 'pricesDeleteCategoryTitle'),
      message: adminFmtN(cfg, 'pricesDeleteCategoryMsg', { 1: data.value.categories[key] ?? key }),
      danger: true,
    });
    if (ok) {
      removeCategory(key);
    }
  }

  async function confirmRemoveZone(zone: number) {
    const ok = await adminConfirm({
      title: adminStr(cfg, 'pricesDeleteZoneTitle'),
      message: adminFmtN(cfg, 'pricesDeleteZoneMsg', { 1: String(zone) }),
      danger: true,
    });
    if (ok) {
      removeZone(zone);
    }
  }

  async function submit() {
    if (!data.value) {
      return;
    }
    error.value = '';
    try {
      const tasks: Promise<unknown>[] = [
        savePrices({
          matrix: data.value.matrix,
          ticket_types: data.value.ticket_types,
          categories: data.value.categories,
          zones: data.value.zones,
          zone_cap: data.value.zone_cap,
          afternoon_return: data.value.afternoon_return,
          ticket_copy_notes: data.value.ticket_copy_notes,
        }),
      ];
      if (settingsSnapshot.value && thresholdDirty.value) {
        tasks.push(
          saveSettings({
            ...settingsSnapshot.value,
            afternoon_return_threshold_minutes: afternoonThresholdMinutes.value,
          }),
        );
      }
      const results = await Promise.all(tasks);
      data.value = results[0] as PricesPayload;
      if (results[1]) {
        settingsSnapshot.value = results[1] as SettingsPayload;
        afternoonThresholdMinutes.value = settingsSnapshot.value.afternoon_return_threshold_minutes;
      }
      ensureMatrixCells(data.value);
      syncAllSnapshots();
      showSaved(adminStr(cfg, 'saved'));
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'saveFailed');
    }
  }

  return {
    cfg,
    data,
    afternoonThresholdMinutes,
    newTicketLabel,
    newCategoryLabel,
    newZone,
    copyZoneFrom,
    copyZoneTo,
    dirty,
    loading,
    error,
    saveMsg,
    ticketKeys,
    categoryKeys,
    zones,
    matrixConfigured,
    zonesBeyondCap,
    load,
    cellValue,
    cellIsEmpty,
    setCell,
    submitAddTicketType,
    submitAddCategory,
    submitAddZone,
    submitCopyZone,
    confirmRemoveTicketType,
    confirmRemoveCategory,
    confirmRemoveZone,
    submit,
  };
}
