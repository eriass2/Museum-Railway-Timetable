import { computed, ref, type ComputedRef, type Ref } from 'vue';
import { useWizardContext } from '../../composables/useWizardContext';
import type { DayTicketData, TripPriceData } from '../../shared/prices';
import type { PriceTableLabels } from '../../shared/priceLabels';
import { printElement } from '../../utils/printElement';
import { shareText } from '../../utils/webShare';
import { downloadTripSummaryPdf } from '../utils/downloadTripSummaryPdf';
import { buildTripSummaryInput } from '../utils/tripSummaryBuild';
import { buildTripSummaryText } from '../utils/tripSummaryText';
import { cfgStr } from '../utils/wizardLabels';

type SummaryExportOptions = {
  dateText: ComputedRef<string>;
  priceData: Ref<TripPriceData | null>;
  dayPrices: Ref<DayTicketData | null>;
  priceLabels: ComputedRef<PriceTableLabels>;
};

export function useSummaryExport(options: SummaryExportOptions) {
  const { store, cfg, config } = useWizardContext();
  const pdfDownloading = ref(false);
  const pdfError = ref('');

  const tripTypeLabel = computed(() =>
    store.tripType === 'return'
      ? cfgStr(cfg, 'tripReturn', 'Tur och retur')
      : cfgStr(cfg, 'tripSingle', 'Enkel resa'),
  );

  const printLabel = computed(() => cfgStr(cfg, 'summaryPrint', 'Skriv ut'));
  const downloadPdfLabel = computed(() => cfgStr(cfg, 'summaryDownloadPdf', 'Ladda ner som PDF'));
  const downloadPdfBusyLabel = computed(() =>
    cfgStr(cfg, 'summaryDownloadingPdf', 'Skapar PDF…'),
  );
  const pdfErrorLabel = computed(() =>
    cfgStr(cfg, 'summaryPdfError', 'Kunde inte skapa PDF. Försök igen eller använd Skriv ut.'),
  );
  const shareLabel = computed(() => cfgStr(cfg, 'summaryShare', 'Dela'));
  const shareCopiedLabel = computed(() =>
    cfgStr(cfg, 'summaryShareCopied', 'Resesammanfattning kopierad till urklipp.'),
  );
  const shareErrorLabel = computed(() =>
    cfgStr(cfg, 'summaryShareError', 'Kunde inte dela resan. Försök igen.'),
  );

  const shareFeedback = ref('');
  const shareFeedbackIsError = ref(false);

  async function onDownloadPdf(): Promise<void> {
    if (pdfDownloading.value) {
      return;
    }
    pdfError.value = '';
    pdfDownloading.value = true;
    try {
      const input = buildTripSummaryInput({
        store,
        cfg: cfg.value,
        dateText: options.dateText.value,
        tripTypeLabel: tripTypeLabel.value,
        priceData: options.priceData.value,
        dayPrices: options.dayPrices.value,
        priceLabels: options.priceLabels.value,
      });
      const ok = await downloadTripSummaryPdf(input, config);
      if (!ok) {
        pdfError.value = pdfErrorLabel.value;
      }
    } catch {
      pdfError.value = pdfErrorLabel.value;
    } finally {
      pdfDownloading.value = false;
    }
  }

  function onPrint(): void {
    printElement('[data-wizard-summary-print]');
  }

  function buildSummaryTextInput() {
    return buildTripSummaryInput({
      store,
      cfg: cfg.value,
      dateText: options.dateText.value,
      tripTypeLabel: tripTypeLabel.value,
      priceData: options.priceData.value,
      dayPrices: options.dayPrices.value,
      priceLabels: options.priceLabels.value,
    });
  }

  async function onShare(): Promise<void> {
    shareFeedback.value = '';
    shareFeedbackIsError.value = false;
    const text = buildTripSummaryText(buildSummaryTextInput());
    const result = await shareText({
      title: cfgStr(cfg, 'stepSummary', 'Din resa'),
      text,
    });
    if (result === 'copied') {
      shareFeedback.value = shareCopiedLabel.value;
      return;
    }
    if (result === 'failed') {
      shareFeedback.value = shareErrorLabel.value;
      shareFeedbackIsError.value = true;
    }
  }

  return {
    tripTypeLabel,
    printLabel,
    downloadPdfLabel,
    downloadPdfBusyLabel,
    shareLabel,
    shareFeedback,
    shareFeedbackIsError,
    pdfDownloading,
    pdfError,
    onPrint,
    onDownloadPdf,
    onShare,
  };
}
