<script setup lang="ts">
import { MrtButton } from '@/components/ui';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

const cfg = adminConfig();

defineEmits<{
  back: [];
}>();
</script>

<template>
  <p class="mrt-admin-back-nav">
    <MrtButton context="admin" variant="link" @click="$emit('back')">
      ← {{ adminStr(cfg, 'backToList') }}
    </MrtButton>
  </p>
</template>

<style scoped>
.mrt-admin-back-nav {
  margin: 0 0 12px;
}
</style>
