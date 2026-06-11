/** Localized strings for {@link MrtPriceTable} (no wizard imports). */
export type PriceTableLabels = {
  title: string;
  titleSuffix: string;
  typeColumnSr: string;
  note: string;
  seniorNote?: string;
  /** Station-specific purchase info (origin station). */
  stationPurchaseNote?: string;
  /** Conditional footnotes from admin ticket copy (student, season, etc.). */
  footnotes?: string[];
  dash: string;
  tickets: Record<string, string>;
  categories: Record<string, string>;
};
