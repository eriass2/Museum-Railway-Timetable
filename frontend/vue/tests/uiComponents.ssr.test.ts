import { describe, expect, it } from 'vitest';
import { createSSRApp, h } from 'vue';
import { renderToString } from 'vue/server-renderer';
import MrtSummaryCard from '../src/components/ui/MrtSummaryCard.vue';
import MrtDetailSegment from '../src/components/ui/MrtDetailSegment.vue';
import MrtExpandTrigger from '../src/components/ui/MrtExpandTrigger.vue';
import MrtVehicleRow from '../src/components/ui/MrtVehicleRow.vue';
import WizardBetaBanner from '../src/wizard/components/WizardBetaBanner.vue';
import WizardFeedbackWidget from '../src/wizard/components/WizardFeedbackWidget.vue';

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

  it('WizardBetaBanner renders badge and optional feedback link', async () => {
    const html = await render(WizardBetaBanner, {
      label: 'Beta',
      text: 'Testas under säsongen.',
      feedbackLabel: 'Rapportera',
      feedbackUrl: 'mailto:test@example.com',
    });
    expect(html).toContain('mrt-journey-wizard__beta');
    expect(html).toContain('Beta');
    expect(html).toContain('href="mailto:test@example.com"');
  });

  it('MrtExpandTrigger renders label and chevron markup', async () => {
    const html = await render(MrtExpandTrigger, {
      expanded: false,
      label: '2 byten',
    });
    expect(html).toContain('mrt-expand-trigger__label');
    expect(html).toContain('2 byten');
    expect(html).toContain('mrt-expand-trigger__chevron');
  });

  it('WizardFeedbackWidget renders feedback button', async () => {
    const html = await render(WizardFeedbackWidget, {
      config: {
        app: 'wizard',
        labels: { feedbackButtonShort: 'Rapportera fel' },
      },
    });
    expect(html).toContain('mrt-wizard-feedback__trigger');
    expect(html).toContain('Rapportera fel');
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
