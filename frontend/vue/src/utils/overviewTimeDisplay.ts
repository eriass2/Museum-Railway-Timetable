/**
 * Split overview time cell text into label tokens (Ca, P, A) and the main value.
 *
 * @package Museum_Railway_Timetable
 */

export type OverviewTimeParts = {
  labels: string[];
  value: string;
};

const LABEL_TOKENS = ['Ca', 'P', 'A'] as const;

/**
 * @param text Formatted stop time from API (e.g. "Ca P 11.13", "X", "—").
 */
export function parseOverviewTimeText(text: string): OverviewTimeParts {
  const trimmed = text.trim();
  if (trimmed === '' || trimmed === '—' || trimmed === '|' || trimmed === 'X') {
    return { labels: [], value: trimmed === '' ? '—' : trimmed };
  }

  const labels: string[] = [];
  let rest = trimmed;

  for (const token of LABEL_TOKENS) {
    const prefix = `${token} `;
    if (rest.startsWith(prefix)) {
      labels.push(token);
      rest = rest.slice(prefix.length);
    }
  }

  return { labels, value: rest };
}
