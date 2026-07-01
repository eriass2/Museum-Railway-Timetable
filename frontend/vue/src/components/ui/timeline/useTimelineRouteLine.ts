import {
  nextTick,
  onMounted,
  onUnmounted,
  ref,
  watch,
  type CSSProperties,
  type Ref,
  type WatchSource,
} from 'vue';

function measureRouteLine(ol: HTMLElement): CSSProperties {
  const nodes = ol.querySelectorAll<HTMLElement>('.mrt-timeline__node');
  if (nodes.length < 2) {
    return { display: 'none' };
  }

  const first = nodes[0].getBoundingClientRect();
  const last = nodes[nodes.length - 1].getBoundingClientRect();
  const olRect = ol.getBoundingClientRect();
  const top = first.top + first.height / 2 - olRect.top;
  const bottom = olRect.bottom - (last.top + last.height / 2);

  return {
    display: 'block',
    top: `${Math.max(0, top)}px`,
    bottom: `${Math.max(0, bottom)}px`,
  };
}

/** One continuous vertical line from first to last timeline node (J22). */
export function useTimelineRouteLine(
  olRef: Ref<HTMLElement | null | undefined>,
  watchSource: WatchSource<unknown>,
) {
  const routeLineStyle = ref<CSSProperties>({ display: 'none' });

  function updateRouteLine(): void {
    const ol = olRef.value;
    if (!ol) {
      routeLineStyle.value = { display: 'none' };
      return;
    }
    routeLineStyle.value = measureRouteLine(ol);
  }

  watch(watchSource, () => nextTick(updateRouteLine), { flush: 'post' });
  onMounted(() => {
    nextTick(updateRouteLine);
    window.addEventListener('resize', updateRouteLine);
  });
  onUnmounted(() => window.removeEventListener('resize', updateRouteLine));

  return { routeLineStyle };
}
