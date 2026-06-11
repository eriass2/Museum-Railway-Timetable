<script setup lang="ts">
import AdminBackNav from './AdminBackNav.vue';
import AdminFormActions from './AdminFormActions.vue';
import { MrtButton } from '@/components/ui';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

defineProps<{
  heading: string;
  submitLabel: string;
}>();

defineEmits<{
  back: [];
  submit: [];
}>();

const cfg = adminConfig();
</script>

<template>
  <AdminBackNav @back="$emit('back')" />
  <h3 class="mrt-admin-entity-editor__heading">{{ heading }}</h3>
  <slot />
  <AdminFormActions>
    <MrtButton context="admin" variant="primary" @click="$emit('submit')">
      {{ submitLabel }}
    </MrtButton>
    <MrtButton context="admin" variant="secondary" @click="$emit('back')">
      {{ adminStr(cfg, 'cancel') }}
    </MrtButton>
  </AdminFormActions>
</template>

<style scoped>
.mrt-admin-entity-editor__heading {
  margin: 0 0 12px;
  font-size: 14px;
  font-weight: 600;
}
</style>
