/**
 * Shared UI primitives — public shortcodes + Vue admin.
 *
 * @see docs/VUE_UI_COMPONENTS.md
 */
export { default as MrtStack } from './MrtStack.vue';
export { default as MrtVisuallyHidden } from './MrtVisuallyHidden.vue';
export { default as MrtAlert } from './MrtAlert.vue';
export { default as MrtAsyncState } from './MrtAsyncState.vue';
export { default as MrtButton } from './MrtButton.vue';
export { default as MrtDot } from './MrtDot.vue';
export { default as MrtAccentButton } from './MrtAccentButton.vue';
export { default as MrtHeading } from './MrtHeading.vue';
export { default as MrtSurfaceCard } from './MrtSurfaceCard.vue';

export type {
  MrtAdminButtonVariant,
  MrtAlertVariant,
  MrtDotColor,
  MrtPublicButtonVariant,
  MrtUiContext,
} from './types';

export {
  mrtAdminButtonClass,
  mrtAdminNoticeClass,
  mrtDotColorFromClass,
  mrtPublicButtonClass,
} from './uiContext';
