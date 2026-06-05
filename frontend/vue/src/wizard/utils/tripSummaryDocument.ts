import type { TripSummaryTextInput } from './tripSummaryText';
import { shouldShowConnectionLegList } from '../../shared/connectionLegDisplay';

const PRINT_STYLES = `
@page { margin: 14mm 12mm; size: A4 portrait; }
body { margin: 0; font-family: Roboto, "Segoe UI", sans-serif; font-size: 9.5pt; line-height: 1.35; color: #000; }
h1 { margin: 0 0 2mm; font-family: "Open Sans", sans-serif; font-size: 14pt; font-weight: 800; }
.meta { margin: 0 0 5mm; padding-bottom: 3mm; border-bottom: 1px solid #ccc; font-size: 9pt; color: #333; }
.card { break-inside: avoid; page-break-inside: avoid; margin: 0 0 3mm; padding: 3mm 4mm; border: 1px solid #bbb; }
.card h2 { margin: 0 0 1.5mm; font-size: 8.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #1f4d2e; }
.time { margin: 0 0 1mm; font-size: 11pt; font-weight: 700; }
.route { margin: 0; font-size: 9pt; color: #333; }
.segments { margin: 1.5mm 0 0; padding: 1.5mm 0 0; border-top: 1px dotted #ccc; list-style: none; padding-left: 0; }
.segments li { margin: 0 0 1mm; font-size: 8.5pt; }
.transfer { font-size: 8pt; background: #fff9c4; padding: 1mm 2mm; }
.prices { break-inside: avoid; margin-top: 3mm; padding-top: 3mm; border-top: 1px solid #ccc; }
.prices h2 { margin: 0 0 2mm; font-size: 9.5pt; font-weight: 700; }
.prices h3 { margin: 2mm 0 1mm; font-size: 8.5pt; font-weight: 700; }
.ticket-type { margin: 0 0 1.5mm; font-size: 8.5pt; font-weight: 700; color: #333; }
.price-table { width: 100%; border-collapse: collapse; margin: 0 0 2mm; font-size: 8.5pt; }
.price-table td { padding: 1mm 0; border-bottom: 1px solid #eee; vertical-align: top; }
.price-label { padding-right: 4mm; }
.price-value { text-align: right; white-space: nowrap; width: 28%; font-weight: 700; }
.note { margin: 2mm 0 0; font-size: 8pt; color: #444; }
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
  if (!leg.segments?.length || !shouldShowConnectionLegList(leg.segments)) {
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

function priceTableHtml(rows: { label: string; value: string }[]): string {
  if (rows.length === 0) {
    return '';
  }
  const body = rows
    .map(
      (row) =>
        `<tr><td class="price-label">${escapeHtml(row.label)}</td>` +
        `<td class="price-value">${escapeHtml(row.value)}</td></tr>`,
    )
    .join('');
  return `<table class="price-table"><tbody>${body}</tbody></table>`;
}

function priceSectionHtml(prices: NonNullable<TripSummaryTextInput['priceSection']>): string {
  if (prices.rows.length === 0) {
    return '';
  }
  const rows = priceTableHtml(prices.rows);
  const dayRows =
    prices.dayTicketHeading && prices.dayTicketRows?.length
      ? `<h3>${escapeHtml(prices.dayTicketHeading)}</h3>${priceTableHtml(prices.dayTicketRows)}`
      : '';
  const note = prices.note ? `<p class="note">${escapeHtml(prices.note)}</p>` : '';
  const ticketType = prices.ticketTypeLabel
    ? `<p class="ticket-type">${escapeHtml(prices.ticketTypeLabel)}</p>`
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
        `<p class="route">${escapeHtml(leg.route)} · ${escapeHtml(leg.date)}</p>${legSegmentsHtml(leg)}</section>`,
    )
    .join('');
  const meta = input.tripTypeLabel
    ? `<p class="meta">${escapeHtml(input.tripTypeLabel)}</p>`
    : '';
  const prices = input.priceSection ? priceSectionHtml(input.priceSection) : '';
  return `<h1>${escapeHtml(input.title)}</h1>${meta}${legs}${prices}`;
}
