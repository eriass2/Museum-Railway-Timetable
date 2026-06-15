import { describe, expect, it } from 'vitest';
import {
  disruptionFeedExpandLabel,
  disruptionFeedGroupByRoute,
  disruptionFeedHasDetailSections,
  disruptionFeedItemCanExpand,
  disruptionFeedItemIntro,
  disruptionFeedItemKindAriaLabel,
  disruptionFeedShowIntro,
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
    route_label: '',
    detail_intro: '',
    detail_sections: [],
    train_numbers: [],
    service_ids: [],
    ...partial,
  };
}

describe('disruptionFeedItemIntro', () => {
  it('prefers detail_intro from API', () => {
    const feedItem = item({
      detail_intro: 'Tågen trafikerar inte enligt ordinarie tidtabell denna dag.',
      body: 'Inställd',
      headline: 'Inställd trafik — Tåg 71',
    });
    expect(disruptionFeedItemIntro(feedItem)).toContain('ordinarie tidtabell');
  });

  it('returns empty intro when detail_intro is missing', () => {
    const feedItem = item({
      body: 'Extra rad som inte ska visas utan detail_intro',
      headline: 'Rubrik',
    });
    expect(disruptionFeedItemIntro(feedItem)).toBe('');
    expect(disruptionFeedShowIntro(feedItem)).toBe(false);
  });
});

describe('disruptionFeed expand helpers', () => {
  it('detects expandable items with intro or detail sections', () => {
    const withIntro = item({
      detail_intro: 'Extra info',
      headline: 'Rubrik',
    });
    expect(disruptionFeedItemCanExpand(withIntro)).toBe(true);
    expect(disruptionFeedExpandLabel(withIntro, { expandMore: 'Mer', expandDetails: 'Detaljer' })).toBe(
      'Mer',
    );

    const withSections = item({
      kind: 'cancelled',
      headline: 'Inställd trafik — Tåg 71, 73',
      detail_sections: [{ title: 'Faringe – Uppsala', lines: ['71 → Faringe'] }],
    });
    expect(disruptionFeedHasDetailSections(withSections)).toBe(true);
    expect(disruptionFeedItemCanExpand(withSections)).toBe(true);
    expect(
      disruptionFeedExpandLabel(withSections, { expandMore: 'Mer', expandDetails: 'Detaljer' }),
    ).toBe('Detaljer');

    const headlineOnly = item({ headline: 'Kort rubrik' });
    expect(disruptionFeedItemCanExpand(headlineOnly)).toBe(false);
  });
});

describe('disruptionFeedItemKindAriaLabel', () => {
  it('maps feed kinds to Swedish labels', () => {
    expect(disruptionFeedItemKindAriaLabel('cancelled')).toBe('Inställd trafik');
    expect(disruptionFeedItemKindAriaLabel('deviation')).toBe('Tur-avvikelse');
    expect(disruptionFeedItemKindAriaLabel('info')).toBe('Information');
  });
});

describe('disruptionFeedGroupByRoute', () => {
  it('groups upcoming items by route label when routes exist', () => {
    const groups = disruptionFeedGroupByRoute(
      [
        item({ id: 'a', route_label: 'Faringe – Uppsala' }),
        item({ id: 'b', route_label: 'Selkné – Faringe' }),
        item({ id: 'c', route_label: 'Faringe – Uppsala' }),
      ],
      'Övrigt',
    );
    expect(groups).toHaveLength(2);
    expect(groups[0]?.items).toHaveLength(2);
  });

  it('keeps flat list when no route labels exist', () => {
    const groups = disruptionFeedGroupByRoute(
      [item({ id: 'a' }), item({ id: 'b' })],
      'Övrigt',
    );
    expect(groups).toHaveLength(1);
    expect(groups[0]?.routeLabel).toBe('');
    expect(groups[0]?.items).toHaveLength(2);
  });
});
