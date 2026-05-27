import type { CalendarDayStatus, JourneyConnection, WizardStep } from '../types';

export type PriceStationZones = Record<string, number[]>;

export type PriceMatrixCell = string | number | null;

export type PriceMatrix = Record<string, Record<string, PriceMatrixCell>>;

export type PriceMatrixByZone = Record<string, Record<string, Record<string, PriceMatrixCell>>>;

export type L10nMap = Record<string, string>;

export type DebugPreset = {
  step?: WizardStep;
  tripType?: string;
  from?: number;
  to?: number;
  fromTitle?: string;
  toTitle?: string;
  date?: string;
  outbound?: JourneyConnection | null;
  inbound?: JourneyConnection | null;
  calendarDays?: Record<string, CalendarDayStatus>;
  calendarYear?: number;
  calendarMonth?: number;
  outboundConnections?: JourneyConnection[];
  returnConnections?: JourneyConnection[];
};

/** Merged PHP wizard + labels JSON for the journey wizard UI. */
export type WizardCfg = {
  monthNames?: string[];
  weekdayAbbrev?: string[];
  priceStationZones?: PriceStationZones;
  priceMatrix?: PriceMatrix;
  priceMatrixByZone?: PriceMatrixByZone;
  priceTickets?: L10nMap;
  priceCategories?: L10nMap;
  trainTypeIcons?: L10nMap;
  trainTypeSlugIcons?: L10nMap;
  debugPresets?: Record<string, DebugPreset>;
  [key: string]: unknown;
};
