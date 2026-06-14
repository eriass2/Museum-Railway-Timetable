import { describe, expect, it } from 'vitest';
import { resolveDisruptionPanels } from '@/utils/disruptionFeedPanels';
import type { DisruptionFeedItem, DisruptionFeedPayload } from '@/api/disruptionFeed';

function item(id: string, overrides: Partial<DisruptionFeedItem> = {}): DisruptionFeedItem {
  return {
    id,
    source: 'general',
    kind: 'info',
    phase: 'ongoing',
    date_from: '2026-06-06',
    date_to: '2026-06-06',
    date_label: 'Idag',
    headline: 'Test',
    body: 'Test',
    route_label: '',
    detail_intro: '',
    detail_sections: [],
    train_numbers: [],
    service_ids: [],
    ...overrides,
  };
}

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

  it('builds panels from ongoing and upcoming when API panels missing', () => {
    const ongoing = item('a', {
      category_key: 'train',
      category_label: 'Tåg',
      severity: 'warning',
    });
    const upcoming = item('b', { phase: 'upcoming', category_key: 'general', category_label: 'Information' });
    const payload: DisruptionFeedPayload = {
      reference_date: '2026-06-06',
      horizon_days: 90,
      end_date: '2026-09-04',
      ongoing: [ongoing],
      upcoming: [upcoming],
      items: [ongoing, upcoming],
      is_empty: false,
    };
    const panels = resolveDisruptionPanels(payload);
    expect(panels).toHaveLength(2);
    expect(panels[0].categories[0].key).toBe('train');
    expect(panels[1].categories[0].key).toBe('general');
  });
});
