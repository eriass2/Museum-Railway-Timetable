import type { TripSummaryTextInput } from './tripSummaryText';

const PRINT_STYLES = `
@page { margin: 14mm 12mm; size: A4 portrait; }
body { margin: 0; font-family: Roboto, "Segoe UI", sans-serif; font-size: 11pt; line-height: 1.4; color: #000; }
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
`;

export function tripSummaryPdfStyles(): string {
  return PRINT_STYLES;
}

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

/** HTML body for print tab / PDF (no document wrapper). */
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
