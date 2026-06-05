import type { TripSummaryTextInput } from './tripSummaryText';

const PRINT_STYLES = `
body { margin: 14mm 12mm; font-family: Roboto, "Segoe UI", sans-serif; font-size: 11pt; line-height: 1.4; color: #000; }
h1 { margin: 0 0 2mm; font-family: "Open Sans", sans-serif; font-size: 16pt; font-weight: 800; }
.meta { margin: 0 0 6mm; padding-bottom: 4mm; border-bottom: 1px solid #ccc; font-size: 10.5pt; color: #333; }
.card { break-inside: avoid; page-break-inside: avoid; margin: 0 0 4mm; padding: 4mm 5mm; border: 1px solid #bbb; }
.card h2 { margin: 0 0 2mm; font-size: 10pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #1f4d2e; }
.time { margin: 0 0 1mm; font-size: 13pt; font-weight: 700; }
.route, .date { margin: 0 0 1mm; font-size: 10.5pt; }
.segments { margin: 2mm 0 0; padding: 2mm 0 0; border-top: 1px dotted #ccc; list-style: none; padding-left: 0; }
.segments li { margin: 0 0 1.5mm; font-size: 10pt; }
.transfer { font-size: 9.5pt; background: #fff9c4; padding: 1mm 2mm; }
.prices { break-inside: avoid; margin-top: 4mm; padding-top: 4mm; border-top: 1px solid #ccc; }
.prices h2 { margin: 0 0 3mm; font-size: 11pt; font-weight: 700; }
.price-row { display: flex; justify-content: space-between; gap: 8mm; padding: 1.5mm 0; border-bottom: 1px solid #eee; font-size: 10pt; }
.note { margin: 3mm 0 0; font-size: 9pt; color: #444; }
@media print { @page { margin: 14mm 12mm; size: A4 portrait; } }
`;

function escapeHtml(text: string): string {
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function legSegmentsHtml(leg: TripSummaryTextInput['legs'][number]): string {
  if (!leg.segments?.length) {
    return '';
  }
  const items = leg.segments
    .map((segment) => {
      if (segment.type === 'transfer') {
        return `<li class="transfer">${escapeHtml(segment.label)}</li>`;
      }
      const detail = segment.leg;
      const routePart = detail.route ? ` (${escapeHtml(detail.route)})` : '';
      return `<li>${escapeHtml(detail.vehicleLabel)} · ${escapeHtml(detail.timeRange)}${routePart}</li>`;
    })
    .join('');
  return `<ul class="segments">${items}</ul>`;
}

function priceSectionHtml(prices: NonNullable<TripSummaryTextInput['priceSection']>): string {
  if (prices.rows.length === 0) {
    return '';
  }
  const rows = prices.rows
    .map(
      (row) =>
        `<div class="price-row"><span>${escapeHtml(row.label)}</span><span>${escapeHtml(row.value)}</span></div>`,
    )
    .join('');
  const dayRows =
    prices.dayTicketHeading && prices.dayTicketRows?.length
      ? `<h3>${escapeHtml(prices.dayTicketHeading)}</h3>${prices.dayTicketRows
          .map(
            (row) =>
              `<div class="price-row"><span>${escapeHtml(row.label)}</span><span>${escapeHtml(row.value)}</span></div>`,
          )
          .join('')}`
      : '';
  const note = prices.note ? `<p class="note">${escapeHtml(prices.note)}</p>` : '';
  const ticketType = prices.ticketTypeLabel
    ? `<p class="route">${escapeHtml(prices.ticketTypeLabel)}</p>`
    : '';
  return `<section class="prices"><h2>${escapeHtml(prices.heading)}</h2>${ticketType}${rows}${dayRows}${note}</section>`;
}

/** HTML body for print tab / share file (no document wrapper). */
export function buildTripSummaryHtml(input: TripSummaryTextInput): string {
  const legs = input.legs
    .map(
      (leg) =>
        `<section class="card"><h2>${escapeHtml(leg.heading)}</h2>` +
        `<p class="time">${escapeHtml(leg.timeRange)}</p>` +
        `<p class="route">${escapeHtml(leg.route)}</p>` +
        `<p class="date">${escapeHtml(leg.date)}</p>${legSegmentsHtml(leg)}</section>`,
    )
    .join('');
  const meta = input.tripTypeLabel
    ? `<p class="meta">${escapeHtml(input.tripTypeLabel)}</p>`
    : '';
  const prices = input.priceSection ? priceSectionHtml(input.priceSection) : '';
  return `<h1>${escapeHtml(input.title)}</h1>${meta}${legs}${prices}`;
}

export function wrapTripSummaryDocument(bodyHtml: string, title: string): string {
  return `<!DOCTYPE html><html lang="sv"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>${escapeHtml(title)}</title><style>${PRINT_STYLES}</style></head><body>${bodyHtml}</body></html>`;
}

export function prefersStandalonePrintTab(): boolean {
  if (typeof window === 'undefined') {
    return false;
  }
  return window.matchMedia('(max-width: 48rem)').matches;
}

/** Open formatted summary in a new tab and trigger print (mobile-friendly PDF flow). */
export function openSummaryPrintTab(documentHtml: string, title: string): boolean {
  if (typeof window === 'undefined') {
    return false;
  }
  const win = window.open('', '_blank', 'noopener,noreferrer');
  if (!win) {
    return false;
  }
  win.document.open();
  win.document.write(documentHtml);
  win.document.close();
  win.document.title = title;
  const triggerPrint = () => {
    win.focus();
    win.print();
  };
  if (win.document.readyState === 'complete') {
    triggerPrint();
  } else {
    win.addEventListener('load', triggerPrint, { once: true });
  }
  return true;
}

function summaryShareFilename(title: string): string {
  const slug = title
    .toLowerCase()
    .replace(/[^a-z0-9åäö]+/gi, '-')
    .replace(/^-+|-+$/g, '');
  return `${slug || 'resa'}.html`;
}

export function canShareHtmlFile(html: string, title: string): boolean {
  if (typeof navigator === 'undefined' || typeof navigator.canShare !== 'function') {
    return false;
  }
  const file = new File([html], summaryShareFilename(title), { type: 'text/html;charset=utf-8' });
  return navigator.canShare({ files: [file] });
}

export async function shareTripSummaryHtmlFile(
  title: string,
  documentHtml: string,
): Promise<'shared' | 'aborted' | 'failed'> {
  if (typeof navigator === 'undefined' || typeof navigator.share !== 'function') {
    return 'failed';
  }
  const file = new File([documentHtml], summaryShareFilename(title), {
    type: 'text/html;charset=utf-8',
  });
  if (typeof navigator.canShare === 'function' && !navigator.canShare({ files: [file] })) {
    return 'failed';
  }
  try {
    await navigator.share({ title, files: [file] });
    return 'shared';
  } catch (err) {
    if (err instanceof DOMException && err.name === 'AbortError') {
      return 'aborted';
    }
    return 'failed';
  }
}

export async function shareTripSummary(
  title: string,
  plainText: string,
  documentHtml: string,
  sharePlainText: (title: string, text: string) => Promise<'shared' | 'aborted' | 'failed'>,
): Promise<'shared' | 'aborted' | 'failed'> {
  if (canShareHtmlFile(documentHtml, title)) {
    const fileResult = await shareTripSummaryHtmlFile(title, documentHtml);
    if (fileResult !== 'failed') {
      return fileResult;
    }
  }
  return sharePlainText(title, plainText);
}
