import type { MrtAdminButtonVariant, MrtAlertVariant, MrtPublicButtonVariant, MrtUiContext } from './types';

const ADMIN_NOTICE: Record<MrtAlertVariant, string> = {
  info: 'notice notice-info',
  error: 'notice notice-error',
  warning: 'notice notice-warning',
  success: 'notice notice-success',
};

const ADMIN_BUTTON: Record<MrtAdminButtonVariant, string> = {
  primary: 'button button-primary',
  secondary: 'button',
  link: 'button-link',
  'link-delete': 'button button-link-delete',
  small: 'button button-small',
};

/** WordPress admin notice classes for MrtAlert. */
export function mrtAdminNoticeClass(variant: MrtAlertVariant): string {
  return ADMIN_NOTICE[variant];
}

/** WordPress admin button classes for MrtButton. */
export function mrtAdminButtonClass(variant: MrtAdminButtonVariant, wide?: boolean): string {
  const base = ADMIN_BUTTON[variant];
  return wide ? `${base} widefat` : base;
}

/** Public accent button BEM modifier. */
export function mrtPublicButtonClass(variant: MrtPublicButtonVariant): string {
  return `mrt-accent-btn mrt-accent-btn--${variant}`;
}

export function isAdminContext(context: MrtUiContext): boolean {
  return context === 'admin';
}

/** Parse `mrt-dot--green` → `green` for MrtDot migration. */
export function mrtDotColorFromClass(dotClass: string): string | null {
  const match = /^mrt-dot--([a-z]+)$/.exec(dotClass.trim());
  return match?.[1] ?? null;
}
