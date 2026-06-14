import type { LineSegment, StopPosition } from './types';

export function lineSegmentForPosition(position: StopPosition): LineSegment {
  switch (position) {
    case 'first':
      return 'down';
    case 'last':
      return 'up';
    case 'middle':
      return 'through';
    case 'only':
      return 'none';
  }
}

export function isTerminalPosition(position: StopPosition): boolean {
  return position !== 'middle';
}
