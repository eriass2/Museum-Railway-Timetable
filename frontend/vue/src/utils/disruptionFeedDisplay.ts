import type { DisruptionFeedItem } from '@/api/disruptionFeed';

export type DisruptionFeedEditHint = {
  path: string;
  label: string;
  query?: Record<string, string>;
};

/** Body text to show under headline (omit repetition already in the headline). */
export function disruptionFeedItemBodyDisplay(item: DisruptionFeedItem): string {
  const body = item.body.trim();
  const headline = item.headline.trim();
  if (body === '' || body === headline) {
    return '';
  }
  if (item.source === 'deviation' && headline.toLowerCase().includes(body.toLowerCase())) {
    return '';
  }
  if (item.source === 'general') {
    const lines = body.split(/\r?\n/);
    const firstLine = (lines[0] ?? '').trim();
    if (firstLine === headline) {
      return lines.slice(1).join('\n').trim();
    }
  }
  return body;
}

export function disruptionFeedShowBody(item: DisruptionFeedItem): boolean {
  return disruptionFeedItemBodyDisplay(item) !== '';
}

export function disruptionFeedItemKindClasses(item: DisruptionFeedItem): Record<string, boolean> {
  return {
    'mrt-traffic-notices__feed-item--cancelled': item.kind === 'cancelled',
    'mrt-traffic-notices__feed-item--deviation': item.kind === 'deviation',
    'mrt-traffic-notices__feed-item--info': item.kind === 'info',
  };
}

export function disruptionFeedEditHref(hint: DisruptionFeedEditHint): string {
  const params = new URLSearchParams(hint.query ?? {});
  const query = params.toString();
  return `#${hint.path}${query ? `?${query}` : ''}`;
}
