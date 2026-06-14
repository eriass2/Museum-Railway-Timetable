import { describe, expect, it } from 'vitest';
import {
  isTerminalPosition,
  lineSegmentForPosition,
} from '../../src/components/ui/timeline/timelinePresentation';

describe('lineSegmentForPosition', () => {
  it('maps stop positions to line segments', () => {
    expect(lineSegmentForPosition('first')).toBe('down');
    expect(lineSegmentForPosition('last')).toBe('up');
    expect(lineSegmentForPosition('middle')).toBe('through');
    expect(lineSegmentForPosition('only')).toBe('none');
  });
});

describe('isTerminalPosition', () => {
  it('treats middle stops as non-terminal', () => {
    expect(isTerminalPosition('middle')).toBe(false);
    expect(isTerminalPosition('first')).toBe(true);
    expect(isTerminalPosition('last')).toBe(true);
    expect(isTerminalPosition('only')).toBe(true);
  });
});
