import type { DisruptionFeedItem } from '@/api/disruptionFeed';

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
