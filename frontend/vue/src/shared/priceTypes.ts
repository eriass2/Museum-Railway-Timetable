export type PriceStationZones = Record<string, number[]>;

export type PriceMatrixCell = string | number | null;

export type PriceMatrix = Record<string, Record<string, PriceMatrixCell>>;

export type PriceMatrixByZone = Record<string, Record<string, Record<string, PriceMatrixCell>>>;

/** Config fields required for price matrix helpers (wizard l10n or future apps). */
export type PriceCfg = {
  priceStationZones?: PriceStationZones;
  priceMatrix?: PriceMatrix;
  priceMatrixByZone?: PriceMatrixByZone;
  priceDash?: string;
  afternoonReturnPrices?: Record<string, number>;
  priceDayTitle?: string;
  priceAfternoonReturnLabel?: string;
  priceAfternoonNote?: string;
};
