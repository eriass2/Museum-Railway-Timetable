<script setup lang="ts">
import { computed, toRef } from 'vue';
import type { DisruptionFeedItem } from '@/api/disruptionFeed';
import MrtExpandTrigger from '@/components/ui/MrtExpandTrigger.vue';
import { useDisruptionFeedItemDetails } from '@/composables/useDisruptionFeedItemExpand';
import {
  disruptionFeedDetailSections,
  disruptionFeedEditHref,
  disruptionFeedExpandLabel,
  disruptionFeedHasDetailSections,
  disruptionFeedItemCanExpand,
  disruptionFeedItemIntro,
  disruptionFeedItemKindAriaLabel,
  disruptionFeedItemKindClasses,
  disruptionFeedShowIntro,
  type DisruptionFeedEditHint,
  type DisruptionFeedItemLabels,
} from '@/utils/disruptionFeedDisplay';

const props = defineProps<{
  item: DisruptionFeedItem;
  labels: DisruptionFeedItemLabels;
  expanded: boolean;
  editHint?: DisruptionFeedEditHint | null;
}>();

const emit = defineEmits<{ toggle: [] }>();

const intro = computed(() => disruptionFeedItemIntro(props.item));
const sections = computed(() => disruptionFeedDetailSections(props.item));
const hasIntro = computed(() => disruptionFeedShowIntro(props.item));
const hasSections = computed(() => disruptionFeedHasDetailSections(props.item));
const canExpand = computed(() => disruptionFeedItemCanExpand(props.item, props.editHint));
const expandLabel = computed(() => disruptionFeedExpandLabel(props.item, props.labels));

const { detailsOpen, showSectionsPanel, onSummaryToggle } = useDisruptionFeedItemDetails(
  toRef(props, 'expanded'),
  hasIntro,
);

const summaryAriaLabel = computed(() => {
  const kind = disruptionFeedItemKindAriaLabel(props.item.kind);
  const parts = [kind, props.item.date_label, props.item.headline].filter(Boolean);
  return parts.join('. ');
});

function toggleInfo(): void {
  const willOpen = !props.expanded;
  emit('toggle');
  onSummaryToggle(willOpen);
}
</script>

<template>
  <li
    class="mrt-traffic-notices__feed-item"
    :class="[
      disruptionFeedItemKindClasses(item),
      { 'is-expanded': expanded },
    ]"
  >
    <button
      v-if="canExpand"
      type="button"
      class="mrt-traffic-notices__summary-row mrt-traffic-notices__summary-row--interactive"
      :aria-expanded="expanded"
      :aria-label="summaryAriaLabel"
      @click="toggleInfo"
    >
      <span class="mrt-traffic-notices__summary">
        <time
          v-if="item.date_label"
          class="mrt-traffic-notices__date"
          :datetime="item.date_from"
        >
          {{ item.date_label }}
        </time>
        <span class="mrt-traffic-notices__headline">{{ item.headline }}</span>
      </span>
    </button>
    <div v-else class="mrt-traffic-notices__summary-row">
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
    </div>
    <MrtExpandTrigger
      v-if="canExpand"
      :expanded="expanded"
      :label="expandLabel"
      @toggle="toggleInfo"
    />
    <div v-if="expanded" class="mrt-traffic-notices__expanded">
      <p v-if="hasIntro" class="mrt-traffic-notices__intro">
        {{ intro }}
      </p>
      <div v-if="hasSections || editHint" class="mrt-traffic-notices__details-wrap">
        <MrtExpandTrigger
          v-if="hasIntro && hasSections"
          variant="link"
          :expanded="detailsOpen"
          :label="labels.expandDetails"
          @toggle="detailsOpen = !detailsOpen"
        />
        <div v-if="showSectionsPanel" class="mrt-traffic-notices__details">
          <article
            v-for="(section, index) in sections"
            :key="`${section.title}-${index}`"
            class="mrt-traffic-notices__detail-section"
          >
            <h4 v-if="section.title" class="mrt-traffic-notices__detail-title">
              {{ section.title }}
            </h4>
            <ul class="mrt-traffic-notices__detail-lines">
              <li v-for="(line, lineIndex) in section.lines" :key="lineIndex">
                {{ line }}
              </li>
            </ul>
          </article>
          <p v-if="editHint" class="mrt-traffic-notices__edit-link">
            <a :href="disruptionFeedEditHref(editHint)">{{ editHint.label }}</a>
          </p>
        </div>
      </div>
    </div>
  </li>
</template>

<style scoped>
@import '@/components/ui/mrtFocusRing.css';

.mrt-traffic-notices__feed-item {
  margin: 0;
  list-style: none;
  border-block-end: 1px solid var(--mrt-wizard-border, #ddd);
  border-inline-start: 4px solid transparent;
}

.mrt-traffic-notices__feed-item--info {
  border-inline-start-color: var(--mrt-color-brand-green, #296310);
}

.mrt-traffic-notices__feed-item--deviation {
  border-inline-start-color: var(--mrt-color-warning-800, #7a4f01);
}

.mrt-traffic-notices__feed-item--cancelled {
  border-inline-start-color: var(--mrt-text-error, #b32d2e);
}

.mrt-traffic-notices__feed-item:last-child {
  border-block-end: none;
}

.mrt-traffic-notices__summary-row {
  padding: 0.65rem 0.9rem;
}

.mrt-traffic-notices__summary-row--interactive {
  display: block;
  width: 100%;
  margin: 0;
  border: 0;
  padding: 0.65rem 0.9rem;
  background: transparent;
  text-align: left;
  cursor: pointer;
  font: inherit;
  color: inherit;
}

.mrt-traffic-notices__feed-item.is-expanded .mrt-traffic-notices__summary-row--interactive {
  background: var(--mrt-color-neutral-200, #d6d6d6);
}

.mrt-traffic-notices__summary {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 0.15rem 0.35rem;
  margin: 0;
  line-height: 1.35;
  font-size: 0.9375rem;
}

.mrt-traffic-notices__date {
  margin: 0;
  font-weight: 400;
  white-space: nowrap;
}

.mrt-traffic-notices__headline {
  flex: 1 1 auto;
  min-width: 0;
  margin: 0;
  font-weight: 600;
}

.mrt-traffic-notices__feed-item--cancelled .mrt-traffic-notices__headline {
  text-decoration: line-through;
  opacity: 0.85;
}

@media (max-width: 30rem) {
  .mrt-traffic-notices__date {
    flex: 0 0 100%;
    white-space: normal;
  }
}

.mrt-traffic-notices__expanded {
  padding: 0 0.9rem 0.75rem;
  background: var(--mrt-wizard-surface, #fff);
}

.mrt-traffic-notices__intro {
  margin: 0;
  line-height: 1.45;
  font-size: 0.875rem;
  color: var(--mrt-color-neutral-700, #505050);
  white-space: pre-line;
}

.mrt-traffic-notices__details-wrap {
  margin-top: 0.35rem;
}

.mrt-traffic-notices__details {
  margin-top: 0.35rem;
  padding-top: 0.35rem;
  border-top: 1px solid var(--mrt-wizard-border, #ddd);
}

.mrt-traffic-notices__detail-section + .mrt-traffic-notices__detail-section {
  margin-top: 0.5rem;
}

.mrt-traffic-notices__detail-title {
  margin: 0 0 0.2rem;
  font-size: 0.8125rem;
  font-weight: 700;
  line-height: 1.35;
  color: var(--mrt-color-neutral-700, #505050);
}

.mrt-traffic-notices__detail-lines {
  margin: 0;
  padding-left: 1.1rem;
  font-size: 0.8125rem;
  line-height: 1.4;
  color: var(--mrt-color-neutral-600, #666);
}

.mrt-traffic-notices__detail-lines li + li {
  margin-top: 0.15rem;
}

.mrt-traffic-notices__edit-link {
  margin: 0.5rem 0 0;
  font-size: 0.8125rem;
}

.mrt-traffic-notices__edit-link a {
  color: var(--mrt-color-brand-green, #296310);
}
</style>
