<template>
  <div class="admin-tool-list">
    <slot />
  </div>
</template>

<style scoped>
.admin-tool-list {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 8px;
  max-width: 22rem;
}

.admin-tool-list :deep(.button) {
  margin: 0;
  justify-content: center;
  text-align: center;
}
</style>
