import type { CalendarDayInfo, CalendarDayStatus, JourneyConnection, WizardStep } from '../types';

import type {
  PriceMatrix,
  PriceMatrixByZone,
  PriceMatrixCell,
  PriceStationZones,
} from '../../shared/priceTypes';

export type { PriceMatrix, PriceMatrixByZone, PriceMatrixCell, PriceStationZones };

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
  calendarDays?: Record<string, CalendarDayInfo | CalendarDayStatus>;
  calendarYear?: number;
  calendarMonth?: number;
  outboundConnections?: JourneyConnection[];
  returnConnections?: JourneyConnection[];
};

/** Known wizard/labels string keys from PHP l10n. */
export type WizardCfgStringKey =
  | 'stepRoute'
  | 'stepDate'
  | 'stepOutbound'
  | 'stepReturn'
  | 'stepSummary'
  | 'loading'
  | 'errorGeneric'
  | 'errorSameStations'
  | 'noConnections'
  | 'showStops'
  | 'hideStops'
  | 'selectTrip'
  | 'noticeLabel'
  | 'durationMinutes'
  | 'outboundHeading'
  | 'returnHeading'
  | 'onDate'
  | 'pleaseStations'
  | 'tripTypeSingle'
  | 'tripTypeReturn'
  | 'routeContext'
  | 'routeDateContext'
  | 'directTrip'
  | 'transferTrip'
  | 'selectedOutbound'
  | 'towards'
  | 'changeAt'
  | 'transferWait'
  | 'passedStations'
  | 'colService'
  | 'colTrainType'
  | 'colDeparture'
  | 'colArrival'
  | 'colStation'
  | 'colActions'
  | 'calendarGridLabel'
  | 'dayDateOk'
  | 'dayDateTraffic'
  | 'dayDateNone'
  | 'tripsCaptionOutbound'
  | 'tripsCaptionReturn'
  | 'btnChooseTripAria'
  | 'btnShowStopsAria'
  | 'legSegmentLabel'
  | 'priceTableTypeColumn'
  | 'priceTitle'
  | 'priceNote'
  | 'priceDash'
  | 'priceZoneLabel'
  | 'needsJs'
  | 'noStations'
  | 'stepNavAria'
  | 'routeTitle'
  | 'routeIntro'
  | 'from'
  | 'to'
  | 'fromPlaceholder'
  | 'toPlaceholder'
  | 'stationSearchAria'
  | 'stationSearchAriaTo'
  | 'tripTypeLegend'
  | 'tripSingle'
  | 'tripReturn'
  | 'searchTrip'
  | 'timetablePageLink'
  | 'showTimetable'
  | 'back'
  | 'thisMonth'
  | 'goToToday'
  | 'calendarEmptyMonth'
  | 'calendarEmptyHint'
  | 'legendOk'
  | 'legendTraffic'
  | 'legendNone'
  | 'ticketCta'
  | 'summaryPrint'
  | 'summaryShare'
  | 'summaryCopy'
  | 'summaryCopyDone'
  | 'summaryShareDone'
  | 'summaryShareFailed'
  | 'summaryPricesHeading'
  | 'calPrevAria'
  | 'calNextAria';

/** Merged PHP wizard + labels JSON for the journey wizard UI. */
export type WizardCfg = Partial<Record<WizardCfgStringKey, string>> & {
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
};
