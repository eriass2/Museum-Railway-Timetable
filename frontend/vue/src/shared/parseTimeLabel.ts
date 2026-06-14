export type TimeLabelCaParts = {
  ca: boolean;
  value: string;
};

const CA_PREFIX = 'Ca ';

/** Split wizard/overview time labels like "Ca 10.00" into prefix and clock portion. */
export function parseTimeLabelCaPrefix(label: string): TimeLabelCaParts {
  const trimmed = label.trim();
  if (trimmed.startsWith(CA_PREFIX)) {
    return { ca: true, value: trimmed.slice(CA_PREFIX.length) };
  }
  return { ca: false, value: trimmed };
}
