import { describe, expect, it } from 'vitest';
import { createSSRApp, h } from 'vue';
import { renderToString } from 'vue/server-renderer';
import WizardTimeline from '../src/wizard/components/WizardTimeline.vue';
import type { TimelineStop } from '../src/wizard/types';

const stops: TimelineStop[] = [
  { station_title: 'Uppsala Östra', time_label: '18.07' },
  { station_title: 'Fyrislund', time_label: 'Ca 18.10', approximate_time: true },
  { station_title: 'Årsta', time_label: 'Ca 18.12', approximate_time: true },
];

describe('WizardTimeline', () => {
  it('renders timeline via formatTimelineStopTime and cfg labels', async () => {
    const app = createSSRApp({
      render: () =>
        h(WizardTimeline, {
          cfg: {
            showStops: 'Visa passerade stationer',
            hideStops: 'Dölj passerade stationer',
          },
          stops,
        }),
    });
    const html = await renderToString(app);

    expect(html).toContain('Uppsala Östra');
    expect(html).toContain('18.07');
    expect(html).toContain('Visa passerade stationer');
    expect(html).toMatch(/<ol[^>]*class="[^"]*mrt-timeline/);
    expect(html).toContain('aria-label="Resans hållplatser"');
    expect(html).toContain('mrt-timeline__node-col--segment-down');
    expect(html).toContain('mrt-timeline__node-col--segment-up');
    expect(html).toContain('mrt-timeline__time-ca');
  });
});
