<script setup lang="ts">
import type { TimetableIndexItem } from '@/types/timetableIndex';

defineProps<{
  items: TimetableIndexItem[];
  showIntro: boolean;
  labels: {
    intro: string;
    navAria: string;
  };
}>();
</script>

<template>
  <div class="mrt-timetable-index">
    <p v-if="showIntro" class="mrt-timetable-index__intro">
      {{ labels.intro }}
    </p>
    <nav :aria-label="labels.navAria">
      <ul class="mrt-timetable-index__list">
        <li
          v-for="(item, index) in items"
          :key="`${item.label}-${index}`"
          class="mrt-timetable-index__item"
          :class="item.modifier ? `mrt-timetable-index__item--${item.modifier}` : undefined"
        >
          <a
            v-if="item.url"
            class="mrt-timetable-index__card"
            :href="item.url"
            :aria-label="`${item.label} — ${item.ariaHint}`"
          >
            <span class="mrt-timetable-index__swatch" aria-hidden="true" />
            <span class="mrt-timetable-index__body">
              <span class="mrt-timetable-index__title">{{ item.label }}</span>
              <span v-if="item.meta" class="mrt-timetable-index__meta">{{ item.meta }}</span>
            </span>
            <span class="mrt-timetable-index__chevron" aria-hidden="true" />
          </a>
          <div v-else class="mrt-timetable-index__card mrt-timetable-index__card--static">
            <span class="mrt-timetable-index__swatch" aria-hidden="true" />
            <span class="mrt-timetable-index__body">
              <span class="mrt-timetable-index__title">{{ item.label }}</span>
              <span v-if="item.meta" class="mrt-timetable-index__meta">{{ item.meta }}</span>
            </span>
            <span class="mrt-timetable-index__chevron" aria-hidden="true" />
          </div>
        </li>
      </ul>
    </nav>
  </div>
</template>
