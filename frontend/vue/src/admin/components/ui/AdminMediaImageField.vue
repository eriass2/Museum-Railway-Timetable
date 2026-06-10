<script setup lang="ts">
import { MrtButton } from '@/components/ui';
import { pickWpMediaImage } from '../../composables/useWpMediaPicker';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

const model = defineModel<string>({ default: '' });
const cfg = adminConfig();

async function onPick(): Promise<void> {
  const url = await pickWpMediaImage({
    title: adminStr(cfg, 'settingsHeroBackgroundPickTitle', 'Välj bakgrundsbild'),
    button: adminStr(cfg, 'settingsHeroBackgroundPickButton', 'Använd bild'),
  });
  if (url) {
    model.value = url;
  }
}

function onClear(): void {
  model.value = '';
}
</script>

<template>
  <div class="mrt-admin-media-field">
    <input
      v-model="model"
      type="url"
      class="large-text mrt-admin-media-field__url"
      :placeholder="adminStr(cfg, 'settingsHeroBackgroundUrlPlaceholder', 'https://…')"
    />
    <div class="mrt-admin-media-field__actions">
      <MrtButton context="admin" variant="secondary" type="button" @click="onPick">
        {{ adminStr(cfg, 'settingsHeroBackgroundChoose', 'Välj bild…') }}
      </MrtButton>
      <MrtButton
        v-if="model"
        context="admin"
        variant="link"
        type="button"
        @click="onClear"
      >
        {{ adminStr(cfg, 'settingsHeroBackgroundClear', 'Ta bort') }}
      </MrtButton>
    </div>
    <img
      v-if="model"
      :src="model"
      alt=""
      class="mrt-admin-media-field__preview"
    />
  </div>
</template>
