export type Html2PdfWorker = {
  set(options: Record<string, unknown>): Html2PdfWorker;
  from(element: HTMLElement): Html2PdfWorker;
  outputPdf(type: string): Promise<unknown>;
  save(): Promise<void>;
};

export type Html2PdfFn = () => Html2PdfWorker;

declare global {
  interface Window {
    MRTHtml2Pdf?: Html2PdfFn;
  }
}

function scriptUrlFromMainBundle(): string | null {
  const scripts = document.querySelectorAll('script[src]');
  for (const node of scripts) {
    const src = node.getAttribute('src') ?? '';
    const match = src.match(/^(.*\/)assets\/main-[^/?]+\.js(?:\?.*)?$/);
    if (match) {
      return `${match[1]}assets/trip-pdf.js`;
    }
  }
  return null;
}

function pdfScriptUrl(configuredUrl?: string): string {
  const fromConfig = configuredUrl?.trim();
  if (fromConfig) {
    return fromConfig;
  }
  const fromMain = scriptUrlFromMainBundle();
  if (fromMain) {
    return fromMain;
  }
  return '/wp-content/plugins/museum-railway-timetable/assets/dist/vue/assets/trip-pdf.js';
}

function loadPdfVendorScript(url: string): Promise<void> {
  if (window.MRTHtml2Pdf) {
    return Promise.resolve();
  }
  const existing = document.querySelector('script[data-mrt-trip-pdf]');
  if (existing) {
    return new Promise((resolve, reject) => {
      if (window.MRTHtml2Pdf) {
        resolve();
        return;
      }
      existing.addEventListener('load', () => resolve(), { once: true });
      existing.addEventListener('error', () => reject(new Error('trip-pdf.js failed')), { once: true });
    });
  }
  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = url;
    script.dataset.mrtTripPdf = '1';
    script.onload = () => resolve();
    script.onerror = () => reject(new Error(`trip-pdf.js failed: ${url}`));
    document.head.appendChild(script);
  });
}

export async function loadHtml2Pdf(configuredUrl?: string): Promise<Html2PdfFn> {
  if (window.MRTHtml2Pdf) {
    return window.MRTHtml2Pdf;
  }
  const url = pdfScriptUrl(configuredUrl);
  await loadPdfVendorScript(url);
  if (!window.MRTHtml2Pdf) {
    throw new Error('MRTHtml2Pdf missing after load');
  }
  return window.MRTHtml2Pdf;
}

/** Warm the lazy PDF vendor on the summary step so the first download is faster. */
export function prefetchHtml2Pdf(configuredUrl?: string): void {
  void loadHtml2Pdf(configuredUrl).catch(() => {
    // Best-effort; explicit download surfaces errors to the user.
  });
}
