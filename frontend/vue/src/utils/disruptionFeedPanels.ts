import type {
  DisruptionFeedCategory,
  DisruptionFeedItem,
  DisruptionFeedPanel,
  DisruptionFeedPayload,
} from '@/api/disruptionFeed';

const CATEGORY_ORDER = ['train', 'bus', 'general'] as const;

function categoryCounts(items: DisruptionFeedItem[]): DisruptionFeedCategory['counts'] {
  let info = 0;
  let warning = 0;
  for (const item of items) {
    if (item.severity === 'warning') {
      warning += 1;
    } else {
      info += 1;
    }
  }
  return { info, warning };
}

function buildCategory(key: string, items: DisruptionFeedItem[]): DisruptionFeedCategory {
  const first = items[0];
  return {
    key,
    label: first.category_label ?? key,
    icon_key: first.icon_key ?? 'diesel',
    counts: categoryCounts(items),
    items,
  };
}

function groupItemsByCategory(items: DisruptionFeedItem[]): DisruptionFeedCategory[] {
  const groups = new Map<string, DisruptionFeedItem[]>();
  for (const item of items) {
    const key = item.category_key ?? 'general';
    const bucket = groups.get(key) ?? [];
    bucket.push(item);
    groups.set(key, bucket);
  }
  const categories: DisruptionFeedCategory[] = [];
  for (const key of CATEGORY_ORDER) {
    const bucket = groups.get(key);
    if (bucket?.length) {
      categories.push(buildCategory(key, bucket));
    }
  }
  for (const [key, bucket] of groups) {
    if (!CATEGORY_ORDER.includes(key as typeof CATEGORY_ORDER[number]) && bucket.length) {
      categories.push(buildCategory(key, bucket));
    }
  }
  return categories;
}

function buildPanel(
  key: DisruptionFeedPanel['key'],
  title: string,
  icon: DisruptionFeedPanel['icon'],
  items: DisruptionFeedItem[],
): DisruptionFeedPanel {
  return {
    key,
    title,
    icon,
    categories: groupItemsByCategory(items),
  };
}

export function resolveDisruptionPanels(
  payload: DisruptionFeedPayload,
  labels?: {
    sectionOngoing?: string;
    sectionUpcoming?: string;
  },
): DisruptionFeedPanel[] {
  if (payload.panels?.length) {
    return payload.panels;
  }
  const panels: DisruptionFeedPanel[] = [];
  if (payload.ongoing.length) {
    panels.push(
      buildPanel(
        'ongoing',
        labels?.sectionOngoing ?? 'Aktuellt trafikläge',
        'clock',
        payload.ongoing,
      ),
    );
  }
  if (payload.upcoming.length) {
    panels.push(
      buildPanel(
        'upcoming',
        labels?.sectionUpcoming ?? 'Planerade avvikelser',
        'calendar',
        payload.upcoming,
      ),
    );
  }
  return panels;
}
