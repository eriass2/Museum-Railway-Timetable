import { describe, expect, it } from 'vitest';
import { createSSRApp, h } from 'vue';
import { renderToString } from 'vue/server-renderer';
import MrtTimeline, { type MrtTimelineStop } from '../src/components/ui/MrtTimeline.vue';
import { ON_REQUEST_INFO_ARIA_LABEL } from '../src/shared/stopTimeFootnotes';

const stops: MrtTimelineStop[] = [
  { station_title: 'Uppsala', departure_time: '10:00' },
  { station_title: 'Marielund', departure_time: '10:20' },
  { station_title: 'Selknä', departure_time: '10:40' },
  { station_title: 'Fjällnora', arrival_time: '11:00' },
];

async function renderTimeline(
  timelineStops: MrtTimelineStop[] = stops,
  expanded = false,
  cancelled = false,
): Promise<string> {
  const app = createSSRApp({
    render: () =>
      h(MrtTimeline, {
        stops: timelineStops,
        formatTime: (s: MrtTimelineStop) => s.departure_time || s.arrival_time || '',
        showStopsLabel: 'Visa passerade stationer',
        hideStopsLabel: 'Dölj passerade stationer',
        listLabel: 'Resans hållplatser',
        startExpanded: expanded,
        cancelled,
      }),
  });
  return renderToString(app);
}

function segmentCount(html: string, segment: string): number {
  const needle = `mrt-timeline__node-col--segment-${segment}`;
  return html.split(needle).length - 1;
}

describe('MrtTimeline', () => {
  it('renders stops as an ordered list with an accessible label', async () => {
    const html = await renderTimeline(stops, false);
    expect(html).toMatch(/<ol[^>]*class="[^"]*mrt-timeline/);
    expect(html).toContain('aria-label="Resans hållplatser"');
    expect(html).toMatch(/<li[^>]*class="[^"]*mrt-timeline__item[^"]*mrt-timeline__row/);
  });

  it('places show-stops link between first and last stop when collapsed', async () => {
    const html = await renderTimeline(stops, false);
    expect(html.indexOf('Uppsala')).toBeLessThan(html.indexOf('Visa passerade stationer'));
    expect(html.indexOf('Visa passerade stationer')).toBeLessThan(html.indexOf('Fjällnora'));
    expect(html).not.toContain('Marielund');
  });

  it('renders middle stops when startExpanded is true', async () => {
    const html = await renderTimeline(stops, true);
    expect(html).toContain('Marielund');
    expect(html).toContain('Selknä');
    expect(html).toContain('Dölj passerade stationer');
  });

  it('renders info icon on stops that need behovsuppehåll', async () => {
    const app = createSSRApp({
      render: () =>
        h(MrtTimeline, {
          stops: [
            { station_title: 'Skolsta', behov_hint: 'pickup' },
            { station_title: 'Faringe', behov_hint: 'dropoff' },
          ],
          formatTime: () => '10:00',
          showStopsLabel: 'Visa',
          hideStopsLabel: 'Dölj',
        }),
    });
    const html = await renderToString(app);
    expect(html).toContain('mrt-timeline__info');
    expect(html).toContain('mrt-info-mark');
    expect(html).toContain(`aria-label="${ON_REQUEST_INFO_ARIA_LABEL}"`);
  });

  it('renders per-row node column for centered vertical line (J22)', async () => {
    const html = await renderTimeline(stops, false);
    expect(html).toContain('mrt-timeline__node-col');
    expect(html).toContain('mrt-timeline__node');
  });

  it('clips line at first and last stop when collapsed', async () => {
    const html = await renderTimeline(stops, false);
    expect(segmentCount(html, 'down')).toBe(1);
    expect(segmentCount(html, 'up')).toBe(1);
    expect(segmentCount(html, 'through')).toBe(0);
  });

  it('uses through segments for middle stops when expanded', async () => {
    const html = await renderTimeline(stops, true);
    expect(segmentCount(html, 'down')).toBe(1);
    expect(segmentCount(html, 'up')).toBe(1);
    expect(segmentCount(html, 'through')).toBe(2);
  });

  it('uses down and up segments for a two-stop journey', async () => {
    const html = await renderTimeline(
      [
        { station_title: 'Uppsala', departure_time: '10:00' },
        { station_title: 'Fjällnora', arrival_time: '11:00' },
      ],
      false,
    );
    expect(segmentCount(html, 'down')).toBe(1);
    expect(segmentCount(html, 'up')).toBe(1);
    expect(segmentCount(html, 'none')).toBe(0);
    expect(html).not.toContain('Visa passerade stationer');
  });

  it('uses no line segment for a single stop', async () => {
    const html = await renderTimeline([{ station_title: 'Uppsala', departure_time: '10:00' }], false);
    expect(segmentCount(html, 'none')).toBe(1);
    expect(segmentCount(html, 'down')).toBe(0);
    expect(segmentCount(html, 'up')).toBe(0);
  });

  it('marks terminal stops with is-terminal emphasis classes', async () => {
    const html = await renderTimeline(stops, true);
    expect(html).toMatch(/class="[^"]*mrt-timeline__time[^"]*is-terminal/);
    expect(html).toMatch(/class="[^"]*(?:mrt-timeline__station[^"]*is-terminal|is-terminal[^"]*mrt-timeline__station)/);
    expect(html).toMatch(/<span class="mrt-timeline__station"[^>]*>Marielund/);
  });

  it('applies cancelled styling on the stop row', async () => {
    const html = await renderTimeline(stops, false, true);
    expect(html).toContain('mrt-timeline__row--cancelled');
  });

  it('stacks Ca prefix above clock digits (J25)', async () => {
    const app = createSSRApp({
      render: () =>
        h(MrtTimeline, {
          stops: [
            { station_title: 'Lövstahagen', departure_time: '10:46' },
            { station_title: 'Marielund', arrival_time: '11:00' },
          ],
          formatTime: (s: MrtTimelineStop) =>
            s.station_title === 'Lövstahagen' ? 'Ca 10.46' : '11.00',
          showStopsLabel: 'Visa',
          hideStopsLabel: 'Dölj',
        }),
    });
    const html = await renderToString(app);
    expect(html).toContain('mrt-timeline__time-ca');
    expect(html).toContain('>Ca<');
    expect(html).toContain('10.46');
  });
});
