import { describe, expect, it } from 'vitest';
import { resolveDisruptionPanels } from '@/utils/disruptionFeedPanels';
import type { DisruptionFeedPayload } from '@/api/disruptionFeed';

describe('resolveDisruptionPanels', () => {
  it('returns API panels when present', () => {
    const payload: DisruptionFeedPayload = {
      reference_date: '2026-06-06',
      horizon_days: 90,
      end_date: '2026-09-04',
      ongoing: [],
      upcoming: [],
      items: [],
      is_empty: false,
      panels: [
        {
          key: 'ongoing',
          title: 'Aktuellt trafikläge',
          icon: 'clock',
          categories: [],
        },
      ],
    };
    expect(resolveDisruptionPanels(payload)).toHaveLength(1);
    expect(resolveDisruptionPanels(payload)[0].title).toBe('Aktuellt trafikläge');
  });

  it('returns empty array when panels missing', () => {
    const payload: DisruptionFeedPayload = {
      reference_date: '2026-06-06',
      horizon_days: 90,
      end_date: '2026-09-04',
      ongoing: [],
      upcoming: [],
      items: [],
      is_empty: true,
    };
    expect(resolveDisruptionPanels(payload)).toEqual([]);
  });
});
