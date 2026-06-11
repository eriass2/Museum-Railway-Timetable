import { describe, expect, it } from 'vitest';
import {
  disruptionFeedItemBodyDisplay,
  disruptionFeedShowBody,
} from '../src/utils/disruptionFeedDisplay';
import type { DisruptionFeedItem } from '../src/api/disruptionFeed';

function item(partial: Partial<DisruptionFeedItem>): DisruptionFeedItem {
  return {
    id: 'test',
    source: 'general',
    kind: 'info',
    phase: 'ongoing',
    date_from: '2026-06-06',
    date_to: '2026-06-06',
    date_label: '2026-06-06',
    headline: '',
    body: '',
    train_numbers: [],
    service_ids: [],
    ...partial,
  };
}

describe('disruptionFeedItemBodyDisplay', () => {
  it('hides body when deviation notice is already in headline', () => {
    const feedItem = item({
      source: 'deviation',
      headline: 'Inställd trafik — Tåg 71',
      body: 'Inställd',
    });
    expect(disruptionFeedItemBodyDisplay(feedItem)).toBe('');
    expect(disruptionFeedShowBody(feedItem)).toBe(false);
  });

  it('strips first line for general notices when it matches headline', () => {
    const feedItem = item({
      source: 'general',
      headline: 'Baninfo sommar',
      body: 'Baninfo sommar\nBerörda anslutningar: Uppsala',
    });
    expect(disruptionFeedItemBodyDisplay(feedItem)).toBe('Berörda anslutningar: Uppsala');
    expect(disruptionFeedShowBody(feedItem)).toBe(true);
  });

  it('shows full body when not redundant', () => {
    const feedItem = item({
      headline: 'Glassrea',
      body: 'Glassrea på stationen idag.',
    });
    expect(disruptionFeedItemBodyDisplay(feedItem)).toBe('Glassrea på stationen idag.');
  });
});
