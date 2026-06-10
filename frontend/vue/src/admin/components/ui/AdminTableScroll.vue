<template>
  <div class="admin-table-scroll">
    <slot />
  </div>
</template>

<style scoped>
.admin-table-scroll {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

@media (max-width: 782px) {
  .admin-table-scroll {
    margin-left: -4px;
    margin-right: -4px;
  }
}
</style>
