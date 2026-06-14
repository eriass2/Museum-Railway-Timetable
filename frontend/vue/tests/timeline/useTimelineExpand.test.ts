import { describe, expect, it } from 'vitest';
import { useTimelineExpand } from '../../src/components/ui/timeline/useTimelineExpand';

describe('useTimelineExpand', () => {
  it('starts collapsed unless startExpanded is true', () => {
    const collapsed = useTimelineExpand(false);
    const expanded = useTimelineExpand(true);

    expect(collapsed.showAllStops.value).toBe(false);
    expect(expanded.showAllStops.value).toBe(true);
  });

  it('toggles showAllStops', () => {
    const { showAllStops, toggleStops } = useTimelineExpand(false);

    toggleStops();
    expect(showAllStops.value).toBe(true);

    toggleStops();
    expect(showAllStops.value).toBe(false);
  });
});
