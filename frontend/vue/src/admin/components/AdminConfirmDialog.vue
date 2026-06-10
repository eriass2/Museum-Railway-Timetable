<script setup lang="ts">
import { useAdminConfirmDialog } from '../composables/adminConfirm';
import { MrtButton } from './ui';

const { open, options, confirm, cancel } = useAdminConfirmDialog();

function onBackdropClick(event: MouseEvent) {
  if (event.target === event.currentTarget) {
    cancel();
  }
}

function onKeydown(event: KeyboardEvent) {
  if (event.key === 'Escape') {
    cancel();
  }
}
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open && options"
      class="mrt-admin-confirm-backdrop"
      role="presentation"
      @click="onBackdropClick"
      @keydown="onKeydown"
    >
      <div
        class="mrt-admin-confirm"
        role="alertdialog"
        :aria-labelledby="'mrt-admin-confirm-title'"
        :aria-describedby="'mrt-admin-confirm-msg'"
        @click.stop
      >
        <h2 id="mrt-admin-confirm-title" class="mrt-admin-confirm__title">
          {{ options.title }}
        </h2>
        <p id="mrt-admin-confirm-msg" class="mrt-admin-confirm__message">
          {{ options.message }}
        </p>
        <p class="mrt-admin-confirm__actions">
          <MrtButton context="admin" variant="secondary" @click="cancel">
            {{ options.cancelLabel }}
          </MrtButton>
          <MrtButton
            context="admin"
            :variant="options.danger ? 'link-delete' : 'primary'"
            @click="confirm"
          >
            {{ options.confirmLabel }}
          </MrtButton>
        </p>
      </div>
    </div>
  </Teleport>
</template>
