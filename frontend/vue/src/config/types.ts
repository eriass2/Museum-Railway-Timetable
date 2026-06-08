/** Shared fields on every Vue mount config from PHP. */
export type MrtRestConfig = {
  restUrl?: string;
  restNonce?: string;
  isDevMode?: boolean;
  /** Present on public mount configs; used for dev logging source labels. */
  app?: MrtVueApp;
  strings?: Record<string, string>;
  /** Lazy-loaded html2pdf bundle (assets/dist/vue/assets/trip-pdf.js). */
  tripPdfUrl?: string;
};

export type MrtVueApp = 'month' | 'overview' | 'wizard' | 'index' | 'traffic_notices';

export type MonthDayMeta = {
  running?: boolean;
  count?: number | string;
  ymd?: string;
  /** Dominant type (legacy / single-type consumers). */
  type?: string;
  /** All timetable types active on this date (sorted). */
  types?: string[];
};

export type MonthLegendType = {
  type: string;
  label: string;
};

export type MonthVueConfig = MrtRestConfig & {
  app: 'month';
  monthUid?: string;
  monthTitle?: string;
  monthAriaLabel?: string;
  tableCaption?: string;
  weekdayHeaders?: string[];
  weekdayFirst?: number;
  weekdayFirstSunday?: number;
  year?: number;
  month?: number;
  daysInMonth?: number;
  startMonday?: boolean;
  atts?: Record<string, unknown>;
  dates?: Record<number, MonthDayMeta>;
  stringsPrevMonth?: string;
  stringsNextMonth?: string;
  legendServiceDay?: string;
  legendCountHint?: string;
  dayServiceCountTitle?: string;
  dayRunningAria?: string;
  legendClickHint?: string;
  legendTimetableTypes?: MonthLegendType[];
  /** Open day panel on load when ?mrt_date=YYYY-MM-DD is present. */
  initialDate?: string;
};

import type { TimetableOverviewPayload } from '../types/timetableOverview';

export type OverviewVueConfig = MrtRestConfig & {
  app: 'overview';
  timetableId: number;
  overview?: TimetableOverviewPayload;
  embedded?: boolean;
};

export type WizardStation = { id: number; title: string };

export type WizardVueConfig = MrtRestConfig & {
  app: 'wizard';
  stations?: WizardStation[];
  ticketUrl?: string;
  timetableId?: number;
  /** Optional URL to a dedicated timetable page (external link on route step). */
  timetablePageUrl?: string;
  embedded?: boolean;
  debug?: string;
  heroSubtitle?: string;
  startOfWeek?: number;
  wizard?: Record<string, unknown>;
  labels?: Record<string, string>;
};

import type { TimetableIndexItem, TimetableIndexLabels } from '../types/timetableIndex';

export type IndexVueConfig = MrtRestConfig & {
  app: 'index';
  showIntro?: boolean;
  items?: TimetableIndexItem[];
  labels?: TimetableIndexLabels;
  emptyMessage?: string;
};

import type { TrafficNoticesLabels } from '../types/trafficNotices';

export type TrafficNoticesVueConfig = MrtRestConfig & {
  app: 'traffic_notices';
  referenceDate?: string;
  days?: number;
  showGeneral?: boolean;
  showDeviations?: boolean;
  title?: string;
  labels?: TrafficNoticesLabels;
};

export type MrtVueConfig =
  | MonthVueConfig
  | OverviewVueConfig
  | WizardVueConfig
  | IndexVueConfig
  | TrafficNoticesVueConfig;

export function isMonthConfig(c: MrtVueConfig): c is MonthVueConfig {
  return c.app === 'month';
}

export function isOverviewConfig(c: MrtVueConfig): c is OverviewVueConfig {
  return c.app === 'overview';
}

export function isWizardConfig(c: MrtVueConfig): c is WizardVueConfig {
  return c.app === 'wizard';
}

export function isIndexConfig(c: MrtVueConfig): c is IndexVueConfig {
  return c.app === 'index';
}

export function isTrafficNoticesConfig(c: MrtVueConfig): c is TrafficNoticesVueConfig {
  return c.app === 'traffic_notices';
}
