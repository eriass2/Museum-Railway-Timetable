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
  | 'transferTripOne'
  | 'transferTripMany'
  | 'selectedOutbound'
  | 'towards'
  | 'changeAt'
  | 'transferWait'
  | 'passedStations'
  | 'onRequestPickupFootnote'
  | 'onRequestDropoffFootnote'
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
  | 'priceNoteSenior'
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
  | 'back'
  | 'stepGoTo'
  | 'defaultTrainType'
  | 'veteranBus'
  | 'thisMonth'
  | 'goToToday'
  | 'calendarEmptyMonth'
  | 'calendarEmptyHint'
  | 'legendOk'
  | 'legendTraffic'
  | 'legendNone'
  | 'ticketCta'
  | 'summaryPrint'
  | 'summaryDownloadPdf'
  | 'summaryPdfError'
  | 'summaryPricesHeading'
  | 'calPrevAria'
  | 'calNextAria'
  | 'feedbackButton'
  | 'feedbackButtonShort'
  | 'feedbackTitle'
  | 'feedbackTypeLegend'
  | 'feedbackTypeBug'
  | 'feedbackTypeSuggestion'
  | 'feedbackClose'
  | 'feedbackMessage'
  | 'feedbackMessageHint'
  | 'feedbackMessageTooShort'
  | 'feedbackEmail'
  | 'feedbackPrivacy'
  | 'feedbackSubmit'
  | 'feedbackCancel'
  | 'feedbackThanks'
  | 'feedbackError';

/** Merged PHP wizard + labels JSON for the journey wizard UI. */
export type WizardCfg = Partial<Record<WizardCfgStringKey, string>> & {
  monthNames?: string[];
  weekdayAbbrev?: string[];
  priceStationZones?: PriceStationZones;
  priceMatrix?: PriceMatrix;
  priceMatrixByZone?: PriceMatrixByZone;
  priceTickets?: L10nMap;
  priceCategories?: L10nMap;
  priceDayTitle?: string;
  priceAfternoonReturnLabel?: string;
  priceAfternoonNote?: string;
  trainTypeIcons?: L10nMap;
  trainTypeSlugIcons?: L10nMap;
  debugPresets?: Record<string, DebugPreset>;
};
