/**
 * @vitest-environment happy-dom
 */
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { ref } from 'vue';
import { useAdminResource } from '../src/admin/composables/useAdminResource';
import { adminConfirm, useAdminConfirmDialog } from '../src/admin/composables/adminConfirm';
import { useAdminSaveNotice } from '../src/admin/composables/useAdminSaveNotice';
import { useAdminRowFlash } from '../src/admin/composables/useAdminRowFlash';
import type { AdminClientConfig } from '../src/admin/types';
import { withSetup } from './helpers/withSetup';

const adminWindow: { mrtAdminVue?: AdminClientConfig } = {};

beforeEach(() => {
  vi.stubGlobal('window', adminWindow);
  adminWindow.mrtAdminVue = {
    restUrl: 'https://example.test/wp-json/museum-railway-timetable/v1',
    restNonce: 'nonce',
    initialRoute: '/dashboard',
    adminBase: 'https://example.test/wp-admin/admin.php?page=mrt_app',
    canManage: true,
    canOperate: true,
    isDevMode: false,
    trainTypeIconUrls: {},
    strings: {
      confirm: 'Bekräfta',
      cancel: 'Avbryt',
      genericError: 'Fel',
    },
  };
});

afterEach(() => {
  delete adminWindow.mrtAdminVue;
  vi.unstubAllGlobals();
  vi.useRealTimers();
});

describe('useAdminResource', () => {
  it('loads data on demand', async () => {
    const { data, load } = useAdminResource({
      fetch: async () => ({ ok: true }),
      immediate: false,
    });

    await load();

    expect(data.value).toEqual({ ok: true });
  });

  it('sets denied message when beforeLoad fails', async () => {
    const { error, load } = useAdminResource({
      fetch: async () => ({ ok: true }),
      beforeLoad: () => false,
      deniedMessage: 'Ingen behörighet',
      immediate: false,
    });

    await load();

    expect(error.value).toBe('Ingen behörighet');
  });
});

describe('adminConfirm', () => {
  it('resolves true when confirmed', async () => {
    const dialog = useAdminConfirmDialog();
    const pending = adminConfirm({ title: 'Ta bort', message: 'Säker?' });
    dialog.confirm();
    await expect(pending).resolves.toBe(true);
  });

  it('resolves false when cancelled', async () => {
    const dialog = useAdminConfirmDialog();
    const pending = adminConfirm({ title: 'Ta bort', message: 'Säker?' });
    dialog.cancel();
    await expect(pending).resolves.toBe(false);
  });
});

describe('useAdminSaveNotice', () => {
  it('clears message after timeout', () => {
    vi.useFakeTimers();
    const { result, unmount } = withSetup(() => useAdminSaveNotice());

    result.show('Sparat');
    expect(result.saveMsg.value).toBe('Sparat');

    vi.advanceTimersByTime(5000);
    expect(result.saveMsg.value).toBe('');

    unmount();
  });
});

describe('useAdminRowFlash', () => {
  it('flashes row id temporarily', () => {
    vi.useFakeTimers();
    const { flashRow, isFlashed } = useAdminRowFlash(1000);

    flashRow(42);
    expect(isFlashed(42)).toBe(true);

    vi.advanceTimersByTime(1000);
    expect(isFlashed(42)).toBe(false);
  });
});
