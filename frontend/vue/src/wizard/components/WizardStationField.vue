<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import type { WizardStation } from '../../config/types';

const props = defineProps<{
  id: string;
  label: string;
  placeholder: string;
  searchAria: string;
  stations: WizardStation[];
  modelValue: number;
  excludeId?: number;
}>();

const emit = defineEmits<{ 'update:modelValue': [number] }>();

const open = ref(false);
const query = ref('');

const selected = computed(() => props.stations.find((s) => s.id === props.modelValue));

watch(
  () => props.modelValue,
  (id) => {
    query.value = id && selected.value ? selected.value.title : '';
  },
  { immediate: true },
);

const filtered = computed(() => {
  const exclude = props.excludeId ?? 0;
  const list = props.stations.filter((s) => s.id !== exclude || s.id === props.modelValue);
  const q = query.value.trim().toLowerCase();
  if (!q) {
    return list;
  }
  return list.filter((s) => s.title.toLowerCase().includes(q));
});

function onInput(): void {
  open.value = true;
  if (!query.value.trim()) {
    emit('update:modelValue', 0);
  }
}

function pick(station: WizardStation): void {
  emit('update:modelValue', station.id);
  query.value = station.title;
  open.value = false;
}

function closeList(): void {
  window.setTimeout(() => {
    open.value = false;
    if (props.modelValue && selected.value) {
      query.value = selected.value.title;
    }
  }, 120);
}
</script>

<template>
  <div class="mrt-form-field mrt-journey-wizard__station-field">
    <label :for="id">{{ label }}</label>
    <div class="mrt-journey-wizard__station-combo">
      <input
        :id="id"
        v-model="query"
        type="text"
        class="mrt-journey-wizard__station-input"
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
        class="mrt-journey-wizard__station-list"
        role="listbox"
      >
        <li
          v-for="s in filtered"
          :key="s.id"
          role="option"
          :aria-selected="s.id === modelValue"
          @mousedown.prevent="pick(s)"
        >
          {{ s.title }}
        </li>
      </ul>
    </div>
  </div>
</template>
