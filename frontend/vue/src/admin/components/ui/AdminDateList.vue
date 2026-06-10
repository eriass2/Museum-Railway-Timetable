<template>
  <ul class="admin-date-list">
    <slot />
  </ul>
</template>

<style scoped>
.admin-date-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.admin-date-list :deep(li) {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f1;
}

.admin-date-list :deep(li:last-child) {
  border-bottom: 0;
}

.admin-date-list :deep(.button-link) {
  margin-left: auto;
  padding: 4px 0;
}
</style>
