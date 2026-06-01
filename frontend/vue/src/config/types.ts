/** Shared fields on every Vue mount config from PHP. */
export type MrtRestConfig = {
  restUrl?: string;
  restNonce?: string;
  strings?: Record<string, string>;
};

export type MrtVueApp = 'month' | 'overview' | 'wizard';

export type MonthDayMeta = {
  running?: boolean;
  count?: number | string;
  ymd?: string;
};

export type MonthVueConfig = MrtRestConfig & {
  app: 'month';
  monthUid?: string;
  monthTitle?: string;
  monthAriaLabel?: string;
  tableCaption?: string;
  prevMonthUrl?: string;
  nextMonthUrl?: string;
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

export type MrtVueConfig = MonthVueConfig | OverviewVueConfig | WizardVueConfig;

export function isMonthConfig(c: MrtVueConfig): c is MonthVueConfig {
  return c.app === 'month';
}

export function isOverviewConfig(c: MrtVueConfig): c is OverviewVueConfig {
  return c.app === 'overview';
}

export function isWizardConfig(c: MrtVueConfig): c is WizardVueConfig {
  return c.app === 'wizard';
}
