/**
 * Train type icon keys vs WordPress slugs.
 *
 * Icon keys match PNG filenames: assets/icons/train-types/{key}.png
 * and PHP `MRT_train_type_icon_keys()` / `MRT_train_type_slug_icon_map()`.
 *
 * Do not pass taxonomy slugs (e.g. `buss`, `ralsbuss`) directly to iconUrls[…]
 * without normalizing — use `normalizeTrainTypeIconKey()` or `trainTypeIconUrl()`.
 */

export type TrainTypeIconKey = 'steam' | 'diesel' | 'railbus' | 'bus';

export const TRAIN_TYPE_ICON_KEYS: readonly TrainTypeIconKey[] = [
  'steam',
  'diesel',
  'railbus',
  'bus',
] as const;

/** WP train type slug (mrt_train_type) → icon file key. */
export const TRAIN_TYPE_SLUG_TO_ICON_KEY: Record<string, TrainTypeIconKey> = {
  angtag: 'steam',
  ralsbuss: 'railbus',
  dieseltag: 'diesel',
  buss: 'bus',
  'ang-diesel': 'diesel',
};

/** Vägbuss (Selknä–Fjällnora) — slug in data, `bus` in iconUrls. */
export const ROAD_BUS_TRAIN_TYPE_SLUG = 'buss';
export const ROAD_BUS_ICON_KEY: TrainTypeIconKey = 'bus';

export function isTrainTypeIconKey(value: string): value is TrainTypeIconKey {
  return (TRAIN_TYPE_ICON_KEYS as readonly string[]).includes(value);
}

export function normalizeTrainTypeIconKey(keyOrSlug: string): TrainTypeIconKey {
  const lower = keyOrSlug.trim().toLowerCase();
  if (!lower) {
    return 'diesel';
  }
  if (isTrainTypeIconKey(lower)) {
    return lower;
  }
  return TRAIN_TYPE_SLUG_TO_ICON_KEY[lower] ?? 'diesel';
}

export function trainTypeIconUrl(
  iconUrls: Record<string, string>,
  keyOrSlug: string,
): string {
  const key = normalizeTrainTypeIconKey(keyOrSlug);
  return iconUrls[key] ?? iconUrls.diesel ?? '';
}
