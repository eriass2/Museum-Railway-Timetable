<script setup lang="ts">
import { useAdminConfirmDialog } from '../composables/adminConfirm';
import { useConfirmDialogDismiss } from '../composables/useConfirmDialogDismiss';
import { MrtButton } from './ui';

const { open, options, confirm, cancel } = useAdminConfirmDialog();
const { onBackdropClick, onKeydown } = useConfirmDialogDismiss(cancel);
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

<style scoped>
.mrt-admin-confirm-backdrop {
  position: fixed;
  inset: 0;
  z-index: 100100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  background: rgba(0, 0, 0, 0.45);
}

.mrt-admin-confirm {
  max-width: 32em;
  width: 100%;
  padding: 16px 20px;
  background: #fff;
  border: 1px solid #c3c4c7;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
}

.mrt-admin-confirm__title {
  margin: 0 0 8px;
  font-size: 1.15em;
}

.mrt-admin-confirm__message {
  margin: 0 0 16px;
  color: #50575e;
}

.mrt-admin-confirm__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: flex-end;
  margin: 0;
}
</style>
