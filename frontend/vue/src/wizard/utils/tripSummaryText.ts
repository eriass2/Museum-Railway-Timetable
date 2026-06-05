import type { ConnectionLegSummaryItem } from '../../shared/connectionLegDisplay';

export type TripSummaryLeg = {
  heading: string;
  timeRange: string;
  route: string;
  date: string;
  segments?: ConnectionLegSummaryItem[];
};

export type TripSummaryPriceRow = {
  label: string;
  value: string;
};

export type TripSummaryTextInput = {
  title: string;
  tripTypeLabel: string;
  legs: TripSummaryLeg[];
  priceSection?: {
    heading: string;
    ticketTypeLabel: string;
    rows: TripSummaryPriceRow[];
    note?: string;
    dayTicketHeading?: string;
    dayTicketRows?: TripSummaryPriceRow[];
  };
};

/** Plain-text trip summary (e.g. accessibility or future export). */
export function buildTripSummaryText(input: TripSummaryTextInput): string {
  const lines: string[] = [input.title];
  if (input.tripTypeLabel) {
    lines.push(input.tripTypeLabel);
  }
  lines.push('');

  for (const leg of input.legs) {
    lines.push(leg.heading, leg.route, leg.timeRange, leg.date);
    if (leg.segments?.length) {
      for (const segment of leg.segments) {
        if (segment.type === 'transfer') {
          lines.push(`  ${segment.label}`);
          continue;
        }
        const detail = segment.leg;
        const routePart = detail.route ? ` (${detail.route})` : '';
        lines.push(`  ${detail.vehicleLabel} · ${detail.timeRange}${routePart}`);
      }
    }
    lines.push('');
  }

  const prices = input.priceSection;
  if (!prices || prices.rows.length === 0) {
    return lines.join('\n').trim();
  }

  lines.push(prices.heading);
  if (prices.ticketTypeLabel) {
    lines.push(prices.ticketTypeLabel);
  }
  for (const row of prices.rows) {
    lines.push(`${row.label}: ${row.value}`);
  }
  if (prices.note) {
    lines.push('', prices.note);
  }
  if (prices.dayTicketHeading && prices.dayTicketRows?.length) {
    lines.push('', prices.dayTicketHeading);
    for (const row of prices.dayTicketRows) {
      lines.push(`${row.label}: ${row.value}`);
    }
  }

  return lines.join('\n').trim();
}
