<script setup lang="ts">
import { computed } from 'vue';
import type { DisruptionFeedItem } from '@/api/disruptionFeed';
import {
  disruptionFeedDetailSections,
  disruptionFeedEditHref,
  disruptionFeedHasDetailSections,
  disruptionFeedItemCanExpand,
  disruptionFeedItemIntro,
  disruptionFeedShowIntro,
  type DisruptionFeedEditHint,
} from '@/utils/disruptionFeedDisplay';
import MrtTfIconClockSmall from './MrtTfIconClockSmall.vue';
import MrtTfLineBadge from './MrtTfLineBadge.vue';

const props = defineProps<{
  item: DisruptionFeedItem;
  expanded: boolean;
  editHint?: DisruptionFeedEditHint | null;
}>();

const emit = defineEmits<{ toggle: [] }>();

const summary = computed(() => props.item.summary?.trim() || props.item.headline.trim());
const validityLabel = computed(() => props.item.validity_label?.trim() || '');
const lineLabel = computed(() => props.item.line_label?.trim() || '');
const intro = computed(() => disruptionFeedItemIntro(props.item));
const sections = computed(() => disruptionFeedDetailSections(props.item));
const canExpand = computed(() => disruptionFeedItemCanExpand(props.item, props.editHint));
const hasIntro = computed(() => disruptionFeedShowIntro(props.item));
const hasSections = computed(() => disruptionFeedHasDetailSections(props.item));

function toggleDetail(): void {
  if (canExpand.value) {
    emit('toggle');
  }
}
</script>

<template>
  <li class="mrt-tf-alert">
    <div class="mrt-tf-alert__main">
      <div class="mrt-tf-alert__line-slot" aria-hidden="true">
        <MrtTfLineBadge v-if="lineLabel" :label="lineLabel" />
      </div>
      <div class="mrt-tf-alert__body">
        <div class="mrt-tf-alert__summary">
          {{ summary }}
        </div>
        <p v-if="validityLabel" class="mrt-tf-alert__validity">
          <MrtTfIconClockSmall />
          <span>{{ validityLabel }}</span>
        </p>
      </div>
      <button
        v-if="canExpand"
        type="button"
        class="mrt-tf-alert__expand"
        :aria-expanded="expanded"
        :aria-label="expanded ? 'Dölj detaljer' : 'Visa detaljer'"
        @click="toggleDetail"
      >
        {{ expanded ? '−' : '+' }}
      </button>
    </div>
    <div v-if="expanded && (hasIntro || hasSections || editHint)" class="mrt-tf-alert__detail">
      <p v-if="hasIntro">{{ intro }}</p>
      <article
        v-for="(section, index) in sections"
        :key="`${section.title}-${index}`"
        class="mrt-tf-alert__detail-section"
      >
        <h4 v-if="section.title" class="mrt-tf-alert__detail-title">
          {{ section.title }}
        </h4>
        <ul class="mrt-tf-alert__detail-lines">
          <li v-for="(line, lineIndex) in section.lines" :key="lineIndex">
            {{ line }}
          </li>
        </ul>
      </article>
      <p v-if="editHint" class="mrt-tf-alert__edit-link">
        <a :href="disruptionFeedEditHref(editHint)">{{ editHint.label }}</a>
      </p>
    </div>
  </li>
</template>

<style scoped>
@import '@/components/ui/mrtFocusRing.css';
</style>
