<script setup lang="ts">
import { computed, ref } from 'vue';
import type { DisruptionFeedItem } from '@/api/disruptionFeed';
import {
  DEFAULT_DISRUPTION_FEED_ITEM_LABELS,
  disruptionFeedGroupByRoute,
  type DisruptionFeedEditHint,
  type DisruptionFeedItemLabels,
  type DisruptionFeedRouteGroup,
} from '@/utils/disruptionFeedDisplay';
import MrtDisruptionFeedItemCard from './MrtDisruptionFeedItemCard.vue';
import MrtSurfaceCard from '@/components/ui/MrtSurfaceCard.vue';

export type { DisruptionFeedEditHint };

export type DisruptionFeedSectionLabels = {
  sectionOngoing: string;
  sectionUpcoming: string;
  item?: Partial<DisruptionFeedItemLabels>;
};

type FeedSection = {
  key: string;
  label: string;
  routeGroups: DisruptionFeedRouteGroup[];
};

const props = defineProps<{
  ongoing: DisruptionFeedItem[];
  upcoming: DisruptionFeedItem[];
  labels: DisruptionFeedSectionLabels;
  editForItem?: (item: DisruptionFeedItem) => DisruptionFeedEditHint | null;
}>();

const itemLabels = computed<DisruptionFeedItemLabels>(() => ({
  ...DEFAULT_DISRUPTION_FEED_ITEM_LABELS,
  ...props.labels.item,
}));

const sections = computed<FeedSection[]>(() => {
  const result: FeedSection[] = [];
  const otherLabel = itemLabels.value.routeOther;
  if (props.ongoing.length) {
    result.push({
      key: 'ongoing',
      label: props.labels.sectionOngoing,
      routeGroups: disruptionFeedGroupByRoute(props.ongoing, otherLabel),
    });
  }
  if (props.upcoming.length) {
    result.push({
      key: 'upcoming',
      label: props.labels.sectionUpcoming,
      routeGroups: disruptionFeedGroupByRoute(props.upcoming, otherLabel),
    });
  }
  return result;
});

function editHint(item: DisruptionFeedItem): DisruptionFeedEditHint | null {
  return props.editForItem?.(item) ?? null;
}

function showRouteHeading(section: FeedSection, group: DisruptionFeedRouteGroup): boolean {
  return section.key === 'upcoming' && group.routeLabel !== '';
}

const expandedItemId = ref<string | null>(null);

function toggleItem(itemId: string): void {
  expandedItemId.value = expandedItemId.value === itemId ? null : itemId;
}
</script>

<template>
  <MrtSurfaceCard flush class="mrt-traffic-notices__feed-card">
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
        <div
          v-for="(group, groupIndex) in section.routeGroups"
          :key="`${section.key}-${group.routeLabel || 'all'}-${groupIndex}`"
          class="mrt-traffic-notices__route-group"
        >
          <h4
            v-if="showRouteHeading(section, group)"
            class="mrt-traffic-notices__route-title"
          >
            {{ group.routeLabel }}
          </h4>
          <ul class="mrt-traffic-notices__list">
            <MrtDisruptionFeedItemCard
              v-for="item in group.items"
              :key="item.id"
              :item="item"
              :labels="itemLabels"
              :expanded="expandedItemId === item.id"
              :edit-hint="editHint(item)"
              @toggle="toggleItem(item.id)"
            />
          </ul>
        </div>
      </section>
    </div>
  </MrtSurfaceCard>
</template>

<style scoped>
.mrt-traffic-notices__feed-card {
  max-width: 36rem;
  background: var(--mrt-wizard-surface, #fff);
  color: var(--mrt-wizard-text, #151515);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.14);
}

.mrt-traffic-notices__feed {
  display: grid;
  gap: 0;
}

.mrt-traffic-notices__section + .mrt-traffic-notices__section {
  border-block-start: 1px solid var(--mrt-wizard-border, #ddd);
}

.mrt-traffic-notices__section-title {
  margin: 0;
  padding: 0.55rem 0.9rem 0.35rem;
  font-size: var(--mrt-font-size-sm, 0.875rem);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--mrt-color-neutral-700, #505050);
  background: var(--mrt-color-neutral-100, #f3f3f3);
}

.mrt-traffic-notices__route-title {
  margin: 0;
  padding: 0.35rem 0.9rem 0.2rem;
  font-size: 0.875rem;
  font-weight: 700;
  line-height: 1.35;
  color: var(--mrt-color-neutral-700, #505050);
}

.mrt-traffic-notices__list {
  list-style: none;
  margin: 0;
  padding: 0;
}
</style>
