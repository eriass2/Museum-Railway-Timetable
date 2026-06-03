import { beforeEach, describe, expect, it, vi } from 'vitest';
import { useMonthCalendar } from '../src/composables/useMonthCalendar';
import type { MonthVueConfig } from '../src/config/types';

vi.mock('../src/api/mrtRest', () => ({
  mrtRestRequest: vi.fn(),
}));

import { mrtRestRequest } from '../src/api/mrtRest';

const baseConfig: MonthVueConfig = {
  restUrl: 'http://example.test/wp-json/museum-railway-timetable/v1/',
  restNonce: 'nonce',
  year: 2026,
  month: 6,
  daysInMonth: 30,
  weekdayFirst: 1,
  weekdayFirstSunday: 0,
  monthTitle: 'Juni 2026',
  monthAriaLabel: 'Juni 2026',
  tableCaption: 'Trafik juni 2026',
  dates: {},
  legendTimetableTypes: [],
  strings: {
    errorLoading: 'Kunde inte ladda kalendern.',
  },
};

describe('useMonthCalendar', () => {
  beforeEach(() => {
    vi.mocked(mrtRestRequest).mockReset();
  });

  it('loadMonth applies payload from REST', async () => {
    vi.mocked(mrtRestRequest).mockResolvedValue({
      success: true,
      data: {
        year: 2026,
        month: 7,
        daysInMonth: 31,
        weekdayFirst: 3,
        weekdayFirstSunday: 5,
        monthTitle: 'Juli 2026',
        monthAriaLabel: 'Juli 2026',
        tableCaption: 'Trafik juli 2026',
        dates: { 4: { types: ['green'] } },
        legendTimetableTypes: [{ slug: 'green', label: 'Grön' }],
      },
    });

    const cal = useMonthCalendar(baseConfig);
    const ok = await cal.loadMonth(2026, 7);

    expect(ok).toBe(true);
    expect(cal.month.value).toBe(7);
    expect(cal.daysInMonth.value).toBe(31);
    expect(cal.dates.value[4]?.types).toEqual(['green']);
    expect(mrtRestRequest).toHaveBeenCalledWith(
      expect.anything(),
      'mrt_get_timetable_month',
      expect.objectContaining({ year: 2026, month: 7 }),
    );
  });

  it('loadMonth sets error when REST fails', async () => {
    vi.mocked(mrtRestRequest).mockResolvedValue({
      success: false,
      message: 'Serverfel',
    });

    const cal = useMonthCalendar(baseConfig);
    const ok = await cal.loadMonth(2026, 8);

    expect(ok).toBe(false);
    expect(cal.error.value).toBe('Serverfel');
  });

  it('shiftMonth loads adjacent month', async () => {
    vi.mocked(mrtRestRequest).mockResolvedValue({
      success: true,
      data: {
        year: 2026,
        month: 7,
        daysInMonth: 31,
        weekdayFirst: 3,
        weekdayFirstSunday: 5,
        monthTitle: 'Juli 2026',
        monthAriaLabel: 'Juli 2026',
        tableCaption: 'Trafik juli 2026',
        dates: {},
        legendTimetableTypes: [],
      },
    });

    const cal = useMonthCalendar(baseConfig);
    await cal.shiftMonth(1);

    expect(mrtRestRequest).toHaveBeenCalledWith(
      expect.anything(),
      'mrt_get_timetable_month',
      expect.objectContaining({ year: 2026, month: 7 }),
    );
  });
});
