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

<style scoped>
.mrt-traffic-notices__feed {
  display: grid;
  gap: var(--mrt-space-md, 1rem);
}

.mrt-traffic-notices__section-title {
  margin: 0 0 var(--mrt-space-sm, 0.5rem);
  font-size: var(--mrt-font-size-md, 1rem);
  font-weight: 700;
}

.mrt-traffic-notices__list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.mrt-traffic-notices__feed-item {
  padding-block: 0.65rem;
  border-block-end: 1px solid var(--mrt-wizard-border, var(--mrt-color-neutral-200, #ddd));
}

.mrt-traffic-notices__feed-item:last-child {
  border-block-end: none;
}

.mrt-traffic-notices__date {
  margin: 0 0 0.2rem;
  font-size: var(--mrt-font-size-sm, 0.875rem);
  font-weight: 600;
  color: var(--mrt-color-neutral-700, #505050);
}

.mrt-traffic-notices__headline {
  margin: 0;
  line-height: 1.45;
  font-weight: 600;
}

.mrt-traffic-notices__body {
  margin: 0.35rem 0 0;
  line-height: 1.45;
  color: var(--mrt-color-neutral-700, #505050);
}

.mrt-traffic-notices__feed-item--cancelled .mrt-traffic-notices__headline {
  text-decoration: line-through;
  opacity: 0.85;
}

.mrt-traffic-notices__edit-link {
  margin: 0.35rem 0 0;
  font-size: var(--mrt-font-size-sm, 0.875rem);
}

.mrt-traffic-notices__edit-link a {
  color: var(--mrt-color-brand-green, #296310);
}
</style>
