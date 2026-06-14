import { describe, expect, it } from 'vitest';
import { createSSRApp, h } from 'vue';
import { renderToString } from 'vue/server-renderer';
import MrtTimeline, { type MrtTimelineStop } from '../src/components/ui/MrtTimeline.vue';

const stops: MrtTimelineStop[] = [
  { station_title: 'Uppsala', departure_time: '10:00' },
  { station_title: 'Marielund', departure_time: '10:20' },
  { station_title: 'Selknä', departure_time: '10:40' },
  { station_title: 'Fjällnora', arrival_time: '11:00' },
];

async function renderTimeline(expanded = false): Promise<string> {
  const app = createSSRApp({
    render: () =>
      h(MrtTimeline, {
        stops,
        formatTime: (s: MrtTimelineStop) => s.departure_time || s.arrival_time || '',
        showStopsLabel: 'Visa passerade stationer',
        hideStopsLabel: 'Dölj passerade stationer',
        startExpanded: expanded,
      }),
  });
  return renderToString(app);
}

describe('MrtTimeline', () => {
  it('places show-stops link between first and last stop when collapsed', async () => {
    const html = await renderTimeline(false);
    expect(html.indexOf('Uppsala')).toBeLessThan(html.indexOf('Visa passerade stationer'));
    expect(html.indexOf('Visa passerade stationer')).toBeLessThan(html.indexOf('Fjällnora'));
    expect(html).not.toContain('Marielund');
  });

  it('renders middle stops when startExpanded is true', async () => {
    const html = await renderTimeline(true);
    expect(html).toContain('Marielund');
    expect(html).toContain('Selknä');
    expect(html).toContain('Dölj passerade stationer');
  });

  it('renders info icon on stops that need behovsuppehåll', async () => {
    const app = createSSRApp({
      render: () =>
        h(MrtTimeline, {
          stops: [
            { station_title: 'Skolsta', on_request_pickup: true },
            { station_title: 'Faringe', on_request_dropoff: true },
          ],
          formatTime: () => '10:00',
          showStopsLabel: 'Visa',
          hideStopsLabel: 'Dölj',
        }),
    });
    const html = await renderToString(app);
    expect(html).toContain('mrt-timeline__info');
    expect(html).toContain('ℹ️');
  });
});
