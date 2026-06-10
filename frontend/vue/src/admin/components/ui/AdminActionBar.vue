<template>
  <div class="admin-action-bar">
    <slot />
  </div>
</template>

<style scoped>
.admin-action-bar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin: 0;
}

.admin-action-bar :deep(.button) {
  margin: 0;
}

@media (max-width: 782px) {
  .admin-action-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .admin-action-bar :deep(.button) {
    width: 100%;
    text-align: center;
    justify-content: center;
  }
}
</style>
