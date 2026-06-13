<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import type { MrtComboboxOption } from './types';

const props = defineProps<{
  id: string;
  modelValue: number;
  options: MrtComboboxOption[];
  placeholder: string;
  searchAria: string;
  excludeId?: number;
}>();

const emit = defineEmits<{ 'update:modelValue': [number] }>();

const open = ref(false);
const query = ref('');
const activeIndex = ref(-1);

const selected = computed(() => props.options.find((o) => o.id === props.modelValue));

watch(
  () => props.modelValue,
  (id) => {
    query.value = id && selected.value ? selected.value.label : '';
  },
  { immediate: true },
);

const filtered = computed(() => {
  const exclude = props.excludeId ?? 0;
  const list = props.options.filter((o) => o.id !== exclude || o.id === props.modelValue);
  const q = query.value.trim().toLowerCase();
  if (!q) {
    return list;
  }
  return list.filter((o) => o.label.toLowerCase().includes(q));
});

const listId = computed(() => `${props.id}-list`);

const activeDescendant = computed(() => {
  if (!open.value || activeIndex.value < 0) {
    return undefined;
  }
  const option = filtered.value[activeIndex.value];
  return option ? optionId(option) : undefined;
});

function optionId(option: MrtComboboxOption): string {
  return `${props.id}-opt-${option.id}`;
}

function onInput(): void {
  open.value = true;
  activeIndex.value = filtered.value.length > 0 ? 0 : -1;
  if (!query.value.trim()) {
    emit('update:modelValue', 0);
  }
}

function pick(option: MrtComboboxOption): void {
  emit('update:modelValue', option.id);
  query.value = option.label;
  open.value = false;
  activeIndex.value = -1;
}

function closeList(): void {
  window.setTimeout(() => {
    open.value = false;
    activeIndex.value = -1;
    if (props.modelValue && selected.value) {
      query.value = selected.value.label;
    }
  }, 120);
}

function onKeydown(event: KeyboardEvent): void {
  if (!open.value && (event.key === 'ArrowDown' || event.key === 'ArrowUp')) {
    open.value = true;
    activeIndex.value = filtered.value.length > 0 ? 0 : -1;
    event.preventDefault();
    return;
  }
  if (!open.value) {
    return;
  }

  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault();
      if (filtered.value.length === 0) {
        return;
      }
      activeIndex.value = Math.min(activeIndex.value + 1, filtered.value.length - 1);
      break;
    case 'ArrowUp':
      event.preventDefault();
      if (filtered.value.length === 0) {
        return;
      }
      activeIndex.value = Math.max(activeIndex.value - 1, 0);
      break;
    case 'Enter':
      if (activeIndex.value >= 0 && filtered.value[activeIndex.value]) {
        event.preventDefault();
        pick(filtered.value[activeIndex.value]);
      }
      break;
    case 'Escape':
      event.preventDefault();
      open.value = false;
      activeIndex.value = -1;
      break;
    default:
      break;
  }
}

watch(filtered, (list) => {
  if (!open.value) {
    return;
  }
  if (list.length === 0) {
    activeIndex.value = -1;
    return;
  }
  if (activeIndex.value < 0 || activeIndex.value >= list.length) {
    activeIndex.value = 0;
  }
});
</script>

<template>
  <div class="mrt-combobox">
    <input
      :id="id"
      v-model="query"
      type="text"
      class="mrt-combobox__input"
      role="combobox"
      :aria-expanded="open"
      :aria-controls="listId"
      :aria-activedescendant="activeDescendant"
      aria-autocomplete="list"
      autocomplete="off"
      :placeholder="placeholder"
      :aria-label="searchAria"
      @input="onInput"
      @focus="open = true"
      @blur="closeList"
      @keydown="onKeydown"
    >
    <ul
      v-show="open && filtered.length"
      :id="listId"
      class="mrt-combobox__list"
      role="listbox"
    >
      <li
        v-for="(option, idx) in filtered"
        :id="optionId(option)"
        :key="option.id"
        class="mrt-combobox__option"
        :class="{ 'mrt-combobox__option--active': idx === activeIndex }"
        role="option"
        :aria-selected="option.id === modelValue"
        @mousedown.prevent="pick(option)"
      >
        {{ option.label }}
      </li>
    </ul>
  </div>
</template>

<style scoped>
@import './mrtFocusRing.css';

.mrt-combobox {
  position: relative;
}

.mrt-combobox__input {
  width: 100%;
  min-height: 3rem;
  padding: 0.65rem 0.85rem;
  border: 2px solid var(--mrt-color-border-on-surface, #767676);
  border-radius: 0;
  background: var(--mrt-wizard-surface, #fff);
  color: var(--mrt-wizard-text, #141414);
  font-size: 1.05rem;
  line-height: 1.3;
  box-sizing: border-box;
}

.mrt-combobox__input::placeholder {
  color: var(--mrt-color-placeholder, #767676);
  opacity: 1;
}

.mrt-combobox__list {
  position: absolute;
  z-index: 20;
  left: 0;
  right: 0;
  top: calc(100% + 2px);
  margin: 0;
  padding: 0;
  max-height: 14rem;
  overflow-y: auto;
  list-style: none;
  background: var(--mrt-wizard-surface, #fff);
  color: var(--mrt-wizard-text, #151515);
  border: 1px solid var(--mrt-wizard-border, #ccc);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
}

.mrt-combobox__option {
  padding: 0.65rem 0.85rem;
  cursor: pointer;
  font-size: 1rem;
}

.mrt-combobox__option:hover,
.mrt-combobox__option--active,
.mrt-combobox__option[aria-selected='true'] {
  background: color-mix(in srgb, var(--mrt-color-accent-500) 35%, transparent);
}
</style>
