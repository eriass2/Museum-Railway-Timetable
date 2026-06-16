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
