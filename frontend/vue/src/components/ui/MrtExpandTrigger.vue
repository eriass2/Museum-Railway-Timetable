<script setup lang="ts">
defineOptions({ inheritAttrs: false });

withDefaults(
  defineProps<{
    expanded: boolean;
    label: string;
    /** Full-width bar (trip card) or inline link (timeline stops). */
    variant?: 'bar' | 'link';
  }>(),
  { variant: 'bar' },
);

defineEmits<{ toggle: [] }>();
</script>

<template>
  <button
    type="button"
    class="mrt-expand-trigger"
    :class="{
      'is-expanded': expanded,
      'mrt-expand-trigger--link': variant === 'link',
    }"
    :aria-expanded="expanded"
    v-bind="$attrs"
    @click="$emit('toggle')"
  >
    <span class="mrt-expand-trigger__label">{{ label }}</span>
    <span class="mrt-expand-trigger__chevron" aria-hidden="true" />
  </button>
</template>

<style scoped>
.mrt-expand-trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  width: 100%;
  border: 0;
  border-top: 1px solid var(--mrt-wizard-border, #ddd);
  padding: 0.55rem 0.9rem;
  background: var(--mrt-color-neutral-100, #f3f3f3);
  color: #151515;
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.2;
  min-height: 0;
  cursor: pointer;
}

.mrt-expand-trigger__label {
  flex: 1 1 auto;
  min-width: 0;
  text-align: left;
}

.mrt-expand-trigger:hover {
  background: var(--mrt-color-neutral-200, #e8e8e8);
}

.mrt-expand-trigger__chevron {
  width: 1.1rem;
  height: 1.1rem;
  border-right: 3px solid currentColor;
  border-bottom: 3px solid currentColor;
  flex-shrink: 0;
  transform: rotate(45deg) translateY(-0.1rem);
}

.mrt-expand-trigger.is-expanded .mrt-expand-trigger__chevron {
  transform: rotate(225deg) translateY(-0.1rem);
}

.mrt-expand-trigger--link {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  width: auto;
  max-width: 100%;
  margin: 0;
  padding: 0.15rem 0;
  border: 0;
  background: transparent;
  color: var(--mrt-color-brand-olive, #807c1c);
  font-size: 0.95rem;
  font-weight: 700;
  line-height: 1.3;
  text-align: left;
  text-decoration: underline;
  text-decoration-thickness: 2px;
  text-underline-offset: 0.16em;
}

.mrt-expand-trigger--link:hover {
  background: transparent;
  color: var(--mrt-color-green-900, #183809);
}

.mrt-expand-trigger--link .mrt-expand-trigger__chevron {
  width: 0.6rem;
  height: 0.6rem;
  border-width: 2px;
  opacity: 0.85;
  transform: rotate(45deg);
}

.mrt-expand-trigger--link.is-expanded .mrt-expand-trigger__chevron {
  transform: rotate(225deg);
}
</style>
