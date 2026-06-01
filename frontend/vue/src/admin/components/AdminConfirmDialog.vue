<script setup lang="ts">
import { useAdminConfirmDialog } from '../composables/adminConfirm';

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
          <button type="button" class="button" @click="cancel">
            {{ options.cancelLabel }}
          </button>
          <button
            type="button"
            class="button"
            :class="options.danger ? 'button-link-delete' : 'button-primary'"
            @click="confirm"
          >
            {{ options.confirmLabel }}
          </button>
        </p>
      </div>
    </div>
  </Teleport>
</template>
