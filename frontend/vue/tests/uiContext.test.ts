import { describe, expect, it } from 'vitest';
import {
  mrtAdminButtonClass,
  mrtAdminNoticeClass,
  mrtDotColorFromClass,
  mrtPublicButtonClass,
} from '@/components/ui/uiContext';

describe('uiContext', () => {
  it('maps public button variants', () => {
    expect(mrtPublicButtonClass('primary')).toBe('mrt-accent-btn mrt-accent-btn--primary');
    expect(mrtPublicButtonClass('select')).toBe('mrt-accent-btn mrt-accent-btn--select');
  });

  it('maps admin button variants', () => {
    expect(mrtAdminButtonClass('primary')).toBe('button button-primary');
    expect(mrtAdminButtonClass('primary', true)).toBe('button button-primary widefat');
    expect(mrtAdminButtonClass('link-delete')).toBe('button button-link-delete');
  });

  it('maps admin notice variants', () => {
    expect(mrtAdminNoticeClass('success')).toBe('notice notice-success');
    expect(mrtAdminNoticeClass('error')).toBe('notice notice-error');
  });

  it('parses dot color from legacy class', () => {
    expect(mrtDotColorFromClass('mrt-dot--green')).toBe('green');
    expect(mrtDotColorFromClass('invalid')).toBeNull();
  });
});
