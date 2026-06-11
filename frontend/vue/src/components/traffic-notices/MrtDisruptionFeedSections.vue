<script setup lang="ts">
import type { DisruptionFeedItem } from '@/api/disruptionFeed';
import {
  disruptionFeedItemBodyDisplay,
  disruptionFeedShowBody,
} from '@/utils/disruptionFeedDisplay';

export type DisruptionFeedEditHint = {
  path: string;
  label: string;
  query?: Record<string, string>;
};

export type DisruptionFeedSectionLabels = {
  sectionOngoing: string;
  sectionUpcoming: string;
};

defineProps<{
  ongoing: DisruptionFeedItem[];
  upcoming: DisruptionFeedItem[];
  labels: DisruptionFeedSectionLabels;
  editForItem?: (item: DisruptionFeedItem) => DisruptionFeedEditHint | null;
}>();

function itemClasses(item: DisruptionFeedItem): Record<string, boolean> {
  return {
    'mrt-traffic-notices__feed-item--cancelled': item.kind === 'cancelled',
    'mrt-traffic-notices__feed-item--deviation': item.kind === 'deviation',
    'mrt-traffic-notices__feed-item--info': item.kind === 'info',
  };
}

function showBody(item: DisruptionFeedItem): boolean {
  return disruptionFeedShowBody(item);
}

function bodyText(item: DisruptionFeedItem): string {
  return disruptionFeedItemBodyDisplay(item);
}

function editHref(hint: DisruptionFeedEditHint): string {
  const params = new URLSearchParams(hint.query ?? {});
  const query = params.toString();
  return `#${hint.path}${query ? `?${query}` : ''}`;
}
</script>

<template>
  <div class="mrt-traffic-notices__feed">
    <section
      v-if="ongoing.length"
      class="mrt-traffic-notices__section"
      :aria-label="labels.sectionOngoing"
    >
      <h3 class="mrt-traffic-notices__section-title">
        {{ labels.sectionOngoing }}
      </h3>
      <ul class="mrt-traffic-notices__list">
        <li
          v-for="item in ongoing"
          :key="item.id"
          class="mrt-traffic-notices__feed-item"
          :class="itemClasses(item)"
        >
          <p class="mrt-traffic-notices__date">{{ item.date_label }}</p>
          <p class="mrt-traffic-notices__headline">{{ item.headline }}</p>
          <p v-if="showBody(item)" class="mrt-traffic-notices__body">
            {{ bodyText(item) }}
          </p>
          <p v-if="editForItem?.(item)" class="mrt-traffic-notices__edit-link">
            <a :href="editHref(editForItem(item)!)">{{ editForItem(item)!.label }}</a>
          </p>
        </li>
      </ul>
    </section>
    <section
      v-if="upcoming.length"
      class="mrt-traffic-notices__section"
      :aria-label="labels.sectionUpcoming"
    >
      <h3 class="mrt-traffic-notices__section-title">
        {{ labels.sectionUpcoming }}
      </h3>
      <ul class="mrt-traffic-notices__list">
        <li
          v-for="item in upcoming"
          :key="item.id"
          class="mrt-traffic-notices__feed-item"
          :class="itemClasses(item)"
        >
          <p class="mrt-traffic-notices__date">{{ item.date_label }}</p>
          <p class="mrt-traffic-notices__headline">{{ item.headline }}</p>
          <p v-if="showBody(item)" class="mrt-traffic-notices__body">
            {{ bodyText(item) }}
          </p>
          <p v-if="editForItem?.(item)" class="mrt-traffic-notices__edit-link">
            <a :href="editHref(editForItem(item)!)">{{ editForItem(item)!.label }}</a>
          </p>
        </li>
      </ul>
    </section>
  </div>
</template>
