<script setup lang="ts">
import { computed } from 'vue';
import type { DisruptionFeedItem } from '@/api/disruptionFeed';
import {
  disruptionFeedEditHref,
  disruptionFeedItemBodyDisplay,
  disruptionFeedItemKindClasses,
  disruptionFeedShowBody,
  type DisruptionFeedEditHint,
} from '@/utils/disruptionFeedDisplay';

export type { DisruptionFeedEditHint };

export type DisruptionFeedSectionLabels = {
  sectionOngoing: string;
  sectionUpcoming: string;
};

const props = defineProps<{
  ongoing: DisruptionFeedItem[];
  upcoming: DisruptionFeedItem[];
  labels: DisruptionFeedSectionLabels;
  editForItem?: (item: DisruptionFeedItem) => DisruptionFeedEditHint | null;
}>();

const sections = computed(() => {
  const result: { key: string; items: DisruptionFeedItem[]; label: string }[] = [];
  if (props.ongoing.length) {
    result.push({ key: 'ongoing', items: props.ongoing, label: props.labels.sectionOngoing });
  }
  if (props.upcoming.length) {
    result.push({ key: 'upcoming', items: props.upcoming, label: props.labels.sectionUpcoming });
  }
  return result;
});

function editHint(item: DisruptionFeedItem): DisruptionFeedEditHint | null {
  return props.editForItem?.(item) ?? null;
}
</script>

<template>
  <div class="mrt-traffic-notices__feed">
    <section
      v-for="section in sections"
      :key="section.key"
      class="mrt-traffic-notices__section"
      :aria-label="section.label"
    >
      <h3 class="mrt-traffic-notices__section-title">
        {{ section.label }}
      </h3>
      <ul class="mrt-traffic-notices__list">
        <li
          v-for="item in section.items"
          :key="item.id"
          class="mrt-traffic-notices__feed-item"
          :class="disruptionFeedItemKindClasses(item)"
        >
          <p class="mrt-traffic-notices__date">{{ item.date_label }}</p>
          <p class="mrt-traffic-notices__headline">{{ item.headline }}</p>
          <p v-if="disruptionFeedShowBody(item)" class="mrt-traffic-notices__body">
            {{ disruptionFeedItemBodyDisplay(item) }}
          </p>
          <p v-if="editHint(item)" class="mrt-traffic-notices__edit-link">
            <a :href="disruptionFeedEditHref(editHint(item)!)">{{ editHint(item)!.label }}</a>
          </p>
        </li>
      </ul>
    </section>
  </div>
</template>
