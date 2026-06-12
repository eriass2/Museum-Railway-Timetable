<template>
  <span class="mrt-visually-hidden">
    <slot />
  </span>
</template>

<style scoped>
.mrt-visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
</style>
