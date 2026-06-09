import { describe, expect, it } from 'vitest';
import { createSSRApp, h } from 'vue';
import { renderToString } from 'vue/server-renderer';
import MrtSummaryCard from '../src/components/ui/MrtSummaryCard.vue';
import MrtDetailSegment from '../src/components/ui/MrtDetailSegment.vue';
import MrtVehicleRow from '../src/components/ui/MrtVehicleRow.vue';

async function render(
  component: Parameters<typeof h>[0],
  props?: Record<string, unknown>,
  slots?: Record<string, () => unknown>,
) {
  const app = createSSRApp({ render: () => h(component, props, slots) });
  return renderToString(app);
}

describe('UI components (SSR smoke)', () => {
  it('MrtSummaryCard renders heading and body', async () => {
    const html = await render(MrtSummaryCard, { heading: 'Utresa' }, {
      default: () => '09:00 – 10:30',
    });
    expect(html).toContain('mrt-summary-card');
    expect(html).toContain('Utresa');
  });

  it('MrtVehicleRow compact hides labels but keeps aria-label', async () => {
    const html = await render(MrtVehicleRow, {
      compact: true,
      items: [{ kind: 'train', label: 'Museitåg 71', iconUrl: '/icon.svg' }],
    });
    expect(html).toContain('mrt-vehicle-row--compact');
    expect(html).toContain('aria-label="Museitåg 71"');
    expect(html).not.toContain('Museitåg 71</span>');
  });

  it('MrtDetailSegment renders title and notice', async () => {
    const html = await render(MrtDetailSegment, {
      title: 'Tåg 101',
      notice: 'Försening',
      noticeLabel: 'Info',
    });
    expect(html).toContain('mrt-detail-segment');
    expect(html).toContain('Tåg 101');
    expect(html).toContain('Försening');
  });
});
