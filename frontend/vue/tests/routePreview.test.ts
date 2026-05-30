import { describe, expect, it } from 'vitest';
import { createSSRApp, h } from 'vue';
import { renderToString } from 'vue/server-renderer';
import RoutePreview from '../src/admin/components/RoutePreview.vue';
import {
  buildRoutePreviewNodes,
  routePreviewTypeLabel,
} from '../src/admin/utils/routePreviewNodes';

describe('routePreviewNodes', () => {
  const map = new Map([
    [1, { title: 'Hultsfred', station_type: 'station' }],
    [2, { title: 'Västervik', station_type: 'halt' }],
  ]);

  it('marks start and end stations', () => {
    const nodes = buildRoutePreviewNodes([1, 2], map, 1, 2);
    expect(nodes[0]?.role).toBe('start');
    expect(nodes[1]?.role).toBe('end');
  });

  it('labels station types in Swedish', () => {
    expect(routePreviewTypeLabel('halt')).toBe('Hållplats');
  });
});

describe('RoutePreview (SSR)', () => {
  it('renders station chain with arrows', async () => {
    const stationsById = new Map([
      [1, { title: 'A', station_type: 'station' }],
      [2, { title: 'B', station_type: '' }],
    ]);
    const app = createSSRApp({
      render: () =>
        h(RoutePreview, {
          stationIds: [1, 2],
          stationsById,
          startStationId: 1,
          endStationId: 2,
        }),
    });
    const html = await renderToString(app);
    expect(html).toContain('mrt-route-preview');
    expect(html).toContain('A');
    expect(html).toContain('B');
    expect(html).toContain('Start');
    expect(html).toContain('Slut');
  });
});
