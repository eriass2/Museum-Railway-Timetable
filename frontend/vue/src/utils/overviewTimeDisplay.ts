/**
 * Split overview time cell text into restriction labels (P, A), Ca, and the time value.
 *
 * @package Museum_Railway_Timetable
 */

export type OverviewTimeParts = {
  restrictions: string[];
  approximate: boolean;
  value: string;
};

const PREFIX_TOKEN = /^(Ca|P|A)\s+/;

/**
 * @param text Formatted stop time from API (e.g. "P Ca 11.13", "Ca 10.00", "X", "—").
 */
export function parseOverviewTimeText(text: string): OverviewTimeParts {
  const trimmed = text.trim();
  if (trimmed === '' || trimmed === '—' || trimmed === '|' || trimmed === 'X') {
    return { restrictions: [], approximate: false, value: trimmed === '' ? '—' : trimmed };
  }

  const labels: string[] = [];
  let rest = trimmed;

  while (PREFIX_TOKEN.test(rest)) {
    const match = rest.match(PREFIX_TOKEN);
    if (!match) {
      break;
    }
    labels.push(match[1]);
    rest = rest.slice(match[0].length);
  }

  return {
    restrictions: labels.filter((label) => label === 'P' || label === 'A'),
    approximate: labels.includes('Ca'),
    value: rest,
  };
}

/**
 * P/A restriction prefix only (Ca is rendered adjacent to the time digits).
 */
export function formatOverviewTimePrefix(parts: OverviewTimeParts): string {
  return parts.restrictions.length ? `${parts.restrictions.join(' ')} ` : '';
}

/**
 * Time value with Ca immediately before the digits when approximate.
 */
export function formatOverviewTimeValue(parts: OverviewTimeParts, approximateTime = false): string {
  const showCa = approximateTime || parts.approximate;
  if (showCa && parts.value !== 'X' && parts.value !== '|' && parts.value !== '—') {
    return `Ca ${parts.value}`;
  }
  return parts.value;
}
