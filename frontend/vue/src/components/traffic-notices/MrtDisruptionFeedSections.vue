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
          <p class="mrt-traffic-notices__summary">
            <time
              v-if="item.date_label"
              class="mrt-traffic-notices__date"
              :datetime="item.date_from"
            >
              {{ item.date_label }}
            </time>
            <span class="mrt-traffic-notices__headline">{{ item.headline }}</span>
          </p>
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

<style scoped>
.mrt-traffic-notices__feed {
  display: grid;
  gap: var(--mrt-space-md, 1rem);
  max-width: 36rem;
}

.mrt-traffic-notices__section-title {
  margin: 0 0 0.35rem;
  font-size: var(--mrt-font-size-sm, 0.875rem);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--mrt-color-neutral-700, #505050);
}

.mrt-traffic-notices__list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.mrt-traffic-notices__feed-item {
  margin: 0;
  padding-block: 0.3rem;
  border-block-end: 1px solid var(--mrt-color-neutral-200, #e8e8e8);
}

.mrt-traffic-notices__feed-item:last-child {
  border-block-end: none;
}

.mrt-traffic-notices__summary {
  margin: 0;
  line-height: 1.35;
  font-size: 0.9375rem;
}

.mrt-traffic-notices__date {
  margin: 0;
  font-weight: 400;
  color: inherit;
  white-space: nowrap;
}

.mrt-traffic-notices__date::after {
  content: '\00a0';
}

.mrt-traffic-notices__headline {
  margin: 0;
  font-weight: 400;
}

.mrt-traffic-notices__body {
  margin: 0.15rem 0 0;
  line-height: 1.35;
  font-size: 0.8125rem;
  color: var(--mrt-color-neutral-600, #666);
}

.mrt-traffic-notices__feed-item--cancelled .mrt-traffic-notices__headline {
  text-decoration: line-through;
  opacity: 0.8;
}

.mrt-traffic-notices__edit-link {
  margin: 0.15rem 0 0;
  font-size: 0.8125rem;
}

.mrt-traffic-notices__edit-link a {
  color: var(--mrt-color-brand-green, #296310);
}
</style>
