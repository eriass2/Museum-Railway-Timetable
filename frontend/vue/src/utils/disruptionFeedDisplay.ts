import type { DisruptionFeedItem } from '@/api/disruptionFeed';

export type DisruptionFeedDetailSection = {
  title: string;
  lines: string[];
};

export type DisruptionFeedEditHint = {
  path: string;
  label: string;
  query?: Record<string, string>;
};

export type DisruptionFeedItemLabels = {
  expandMore: string;
  expandDetails: string;
  routeOther: string;
};

export const DEFAULT_DISRUPTION_FEED_ITEM_LABELS: DisruptionFeedItemLabels = {
  expandMore: 'Mer information',
  expandDetails: 'Visa detaljer',
  routeOther: 'Övrigt',
};

export function disruptionFeedItemIntro(item: DisruptionFeedItem): string {
  return item.detail_intro?.trim() ?? '';
}

export function disruptionFeedShowIntro(item: DisruptionFeedItem): boolean {
  return disruptionFeedItemIntro(item) !== '';
}

export function disruptionFeedDetailSections(item: DisruptionFeedItem): DisruptionFeedDetailSection[] {
  return (item.detail_sections ?? []).filter(
    (section) => section.lines.length > 0,
  );
}

export function disruptionFeedHasDetailSections(item: DisruptionFeedItem): boolean {
  return disruptionFeedDetailSections(item).length > 0;
}

export function disruptionFeedItemKindClasses(item: DisruptionFeedItem): Record<string, boolean> {
  return {
    'mrt-traffic-notices__feed-item--cancelled': item.kind === 'cancelled',
    'mrt-traffic-notices__feed-item--deviation': item.kind === 'deviation',
    'mrt-traffic-notices__feed-item--info': item.kind === 'info',
  };
}

export function disruptionFeedItemKindAriaLabel(kind: DisruptionFeedItem['kind']): string {
  switch (kind) {
    case 'cancelled':
      return 'Inställd trafik';
    case 'deviation':
      return 'Tur-avvikelse';
    default:
      return 'Information';
  }
}

export function disruptionFeedEditHref(hint: DisruptionFeedEditHint): string {
  const params = new URLSearchParams(hint.query ?? {});
  const query = params.toString();
  return `#${hint.path}${query ? `?${query}` : ''}`;
}

export function disruptionFeedItemCanExpand(
  item: DisruptionFeedItem,
  editHint?: DisruptionFeedEditHint | null,
): boolean {
  return (
    disruptionFeedShowIntro(item) ||
    disruptionFeedHasDetailSections(item) ||
    editHint != null
  );
}

export function disruptionFeedExpandLabel(
  item: DisruptionFeedItem,
  labels: Pick<DisruptionFeedItemLabels, 'expandMore' | 'expandDetails'>,
): string {
  if (disruptionFeedShowIntro(item)) {
    return labels.expandMore;
  }
  if (disruptionFeedHasDetailSections(item)) {
    return labels.expandDetails;
  }
  return labels.expandMore;
}

export type DisruptionFeedRouteGroup = {
  routeLabel: string;
  items: DisruptionFeedItem[];
};

export function disruptionFeedGroupByRoute(
  items: DisruptionFeedItem[],
  otherLabel: string,
): DisruptionFeedRouteGroup[] {
  const hasNamedRoute = items.some((item) => (item.route_label?.trim() ?? '') !== '');
  if (!hasNamedRoute) {
    return [{ routeLabel: '', items }];
  }
  const groups = new Map<string, DisruptionFeedItem[]>();
  for (const item of items) {
    const key = item.route_label?.trim() || otherLabel;
    const bucket = groups.get(key) ?? [];
    bucket.push(item);
    groups.set(key, bucket);
  }
  return [...groups.entries()].map(([routeLabel, groupItems]) => ({
    routeLabel: routeLabel === otherLabel ? '' : routeLabel,
    items: groupItems,
  }));
}
