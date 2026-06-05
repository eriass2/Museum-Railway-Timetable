import { buildTripSummaryHtml, tripSummaryPdfStyles } from './tripSummaryDocument';
import { loadHtml2Pdf } from './loadHtml2Pdf';
import type { TripSummaryTextInput } from './tripSummaryText';

export type DownloadTripSummaryPdfOptions = {
  tripPdfUrl?: string;
};

/** Slug for PDF download filename (ASCII-safe, preserves Swedish letters as a/o). */
export function tripSummaryPdfFilename(tripName: string): string {
  const slug = tripName
    .toLowerCase()
    .replace(/å/g, 'a')
    .replace(/ä/g, 'a')
    .replace(/ö/g, 'o')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
  return `${slug || 'resa'}.pdf`;
}

function downloadBlob(blob: Blob, filename: string): void {
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  link.rel = 'noopener';
  document.body.appendChild(link);
  link.click();
  link.remove();
  window.setTimeout(() => URL.revokeObjectURL(url), 2000);
}

function mountPdfIframe(bodyHtml: string): HTMLIFrameElement {
  const iframe = document.createElement('iframe');
  iframe.setAttribute('aria-hidden', 'true');
  iframe.title = '';
  iframe.style.cssText =
    'position:fixed;left:0;top:0;width:210mm;height:297mm;border:0;opacity:0;pointer-events:none;z-index:-1;';
  document.body.appendChild(iframe);

  const doc = iframe.contentDocument;
  if (!doc) {
    iframe.remove();
    throw new Error('PDF iframe unavailable');
  }

  doc.open();
  doc.write(
    `<!DOCTYPE html><html lang="sv"><head><meta charset="utf-8">` +
      `<style>${tripSummaryPdfStyles()}</style></head><body>${bodyHtml}</body></html>`,
  );
  doc.close();
  return iframe;
}

async function waitForIframeLayout(iframe: HTMLIFrameElement): Promise<HTMLElement> {
  const doc = iframe.contentDocument;
  const body = doc?.body;
  if (!body) {
    throw new Error('PDF iframe body missing');
  }
  if (doc.fonts?.ready) {
    await doc.fonts.ready;
  }
  await new Promise<void>((resolve) => {
    requestAnimationFrame(() => requestAnimationFrame(() => resolve()));
  });
  return body;
}

/** Generate and download a PDF without browser print headers/footers. */
export async function downloadTripSummaryPdf(
  input: TripSummaryTextInput,
  options?: DownloadTripSummaryPdfOptions,
): Promise<boolean> {
  if (typeof document === 'undefined') {
    return false;
  }

  const bodyHtml = buildTripSummaryHtml(input);
  const iframe = mountPdfIframe(bodyHtml);
  try {
    const body = await waitForIframeLayout(iframe);
    const html2pdf = await loadHtml2Pdf(options?.tripPdfUrl);
    const blob = (await html2pdf()
      .set({
        margin: [14, 12, 14, 12],
        filename: tripSummaryPdfFilename(input.downloadName),
        image: { type: 'jpeg', quality: 0.96 },
        html2canvas: { scale: 2, logging: false, useCORS: true, scrollX: 0, scrollY: 0 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak: { mode: ['avoid-all', 'css', 'legacy'] },
      })
      .from(body)
      .outputPdf('blob')) as Blob;

    if (!(blob instanceof Blob) || blob.size === 0) {
      throw new Error('Empty PDF blob');
    }
    downloadBlob(blob, tripSummaryPdfFilename(input.downloadName));
    return true;
  } finally {
    iframe.remove();
  }
}
