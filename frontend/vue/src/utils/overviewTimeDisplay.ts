/**
 * Split overview time cell text into Ca, clock digits, and footnote suffix (P, A, X).
 */

import { parseTimeLabelCaPrefix } from '../shared/parseTimeLabel';

export type OverviewTimeParts = {
  approximate: boolean;
  value: string;
  suffix: string;
};

const LEGACY_PREFIX = /^(Ca|P|A)\s+/;
const SUFFIX_TOKEN = /\s+(P|A|X)$/;

/**
 * @param text Formatted stop time from API (e.g. "Ca 10.09 X", "10.00 P", "X", "—").
 */
export function parseOverviewTimeText(text: string): OverviewTimeParts {
  const trimmed = text.trim();
  if (trimmed === '' || trimmed === '—' || trimmed === '|' || trimmed === 'X') {
    return {
      approximate: false,
      value: trimmed === '' ? '—' : trimmed,
      suffix: '',
    };
  }

  let rest = trimmed;
  const legacyLabels: string[] = [];
  while (LEGACY_PREFIX.test(rest)) {
    const match = rest.match(LEGACY_PREFIX);
    if (!match) {
      break;
    }
    legacyLabels.push(match[1]);
    rest = rest.slice(match[0].length);
  }

  let approximate = legacyLabels.includes('Ca');
  let suffix = '';
  const suffixMatch = rest.match(SUFFIX_TOKEN);
  if (suffixMatch) {
    suffix = suffixMatch[1];
    rest = rest.slice(0, -suffixMatch[0].length);
  } else if (legacyLabels.includes('P') || legacyLabels.includes('A')) {
    suffix = legacyLabels.find((label) => label === 'P' || label === 'A') ?? '';
  }

  if (rest.startsWith('Ca ')) {
    const caParts = parseTimeLabelCaPrefix(rest);
    approximate = caParts.ca;
    rest = caParts.value;
  }

  return {
    approximate,
    value: rest,
    suffix,
  };
}

/** @deprecated Use formatOverviewTimeSuffix — kept for callers migrating from prefix layout. */
export function formatOverviewTimePrefix(_parts: OverviewTimeParts): string {
  return '';
}

/**
 * Clock digits with Ca immediately before when approximate.
 */
export function formatOverviewTimeValue(parts: OverviewTimeParts, approximateTime = false): string {
  const showCa = approximateTime || parts.approximate;
  if (showCa && parts.value !== 'X' && parts.value !== '|' && parts.value !== '—') {
    return `Ca ${parts.value}`;
  }
  return parts.value;
}

/** P/A/X footnote mark (spacing via `.mrt-ov-time__suffix` margin). */
export function formatOverviewTimeSuffix(parts: OverviewTimeParts): string {
  return parts.suffix;
}
