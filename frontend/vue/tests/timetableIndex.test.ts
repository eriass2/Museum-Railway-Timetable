import { describe, expect, it } from 'vitest';
import { mrtDotColorFromClass } from '@/components/ui/uiContext';

describe('timetable index helpers', () => {
  it('parses dot modifier colors for legend', () => {
    expect(mrtDotColorFromClass('mrt-dot--green')).toBe('green');
    expect(mrtDotColorFromClass('mrt-dot--yellow')).toBe('yellow');
  });
});
