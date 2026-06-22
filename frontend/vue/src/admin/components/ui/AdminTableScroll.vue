<template>
  <div class="admin-table-scroll">
    <slot />
  </div>
</template>

<style scoped>
.admin-table-scroll {
  display: block;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  max-width: 100%;
  min-width: 0;
  width: 100%;
  box-sizing: border-box;
}

@media (max-width: 782px) {
  .admin-table-scroll {
    width: 100%;
  }
}
</style>
