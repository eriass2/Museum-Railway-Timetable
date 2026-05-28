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

function onInput(): void {
  open.value = true;
  if (!query.value.trim()) {
    emit('update:modelValue', 0);
  }
}

function pick(option: MrtComboboxOption): void {
  emit('update:modelValue', option.id);
  query.value = option.label;
  open.value = false;
}

function closeList(): void {
  window.setTimeout(() => {
    open.value = false;
    if (props.modelValue && selected.value) {
      query.value = selected.value.label;
    }
  }, 120);
}
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
      :aria-controls="`${id}-list`"
      autocomplete="off"
      :placeholder="placeholder"
      :aria-label="searchAria"
      @input="onInput"
      @focus="open = true"
      @blur="closeList"
    >
    <ul
      v-show="open && filtered.length"
      :id="`${id}-list`"
      class="mrt-combobox__list"
      role="listbox"
    >
      <li
        v-for="option in filtered"
        :key="option.id"
        class="mrt-combobox__option"
        role="option"
        :aria-selected="option.id === modelValue"
        @mousedown.prevent="pick(option)"
      >
        {{ option.label }}
      </li>
    </ul>
  </div>
</template>
