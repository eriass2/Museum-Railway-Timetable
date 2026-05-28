import { describe, expect, it, vi } from 'vitest';
import { useTimetableOverview } from '../src/composables/useTimetableOverview';

vi.mock('../src/composables/useMrtAjax', () => ({
  useMrtAjax: () => ({
    loading: { value: false },
    error: { value: '' },
    run: vi.fn(async (action: string) => {
      if (action === 'mrt_timetable_overview_data') {
        return {
          success: true,
          data: {
            overview: {
              scope: 'timetable',
              timetableId: 42,
              title: 'Test',
              dateYmd: '2026-05-25',
              timetableType: 'green',
              typeBanner: { label: 'GRÖN' },
              printKey: [],
              iconUrls: {},
              groups: [],
            },
          },
        };
      }
      return { success: false };
    }),
    clearError: vi.fn(),
  }),
}));

describe('useTimetableOverview', () => {
  it('fetchOverview loads JSON payload', async () => {
    const config = { ajaxurl: '/ajax', nonce: 'n' };
    const { overview, fetchOverview } = useTimetableOverview(config);
    const ok = await fetchOverview(42);
    expect(ok).toBe(true);
    expect(overview.value?.timetableId).toBe(42);
  });

  it('fetchOverview rejects invalid timetable id', async () => {
    const config = { ajaxurl: '/ajax', nonce: 'n', strings: {} };
    const { fetchOverview, error } = useTimetableOverview(config);
    const ok = await fetchOverview(0);
    expect(ok).toBe(false);
    expect(error.value).not.toBe('');
  });
});
