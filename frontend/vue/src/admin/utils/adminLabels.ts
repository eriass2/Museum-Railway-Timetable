import type { MaybeRef } from 'vue';
import { unref } from 'vue';
import { resolveMrtString } from '../../utils/mrtStrings';
import type { AdminClientConfig } from '../types';

export function adminStr(
  cfg: MaybeRef<AdminClientConfig>,
  key: string,
  fallback = '',
): string {
  return resolveMrtString({ strings: unref(cfg).strings }, key, fallback);
}

/** Replace `%s` placeholders in order (WordPress-style templates from PHP). */
export function adminFmt(
  cfg: MaybeRef<AdminClientConfig>,
  key: string,
  ...args: (string | number)[]
): string {
  let text = adminStr(cfg, key);
  for (const arg of args) {
    text = text.replace('%s', String(arg));
  }
  return text;
}

/** Replace `%1$s`, `%2$s`, … placeholders (WordPress ordered templates). */
export function adminFmtN(
  cfg: MaybeRef<AdminClientConfig>,
  key: string,
  args: Record<number, string | number>,
): string {
  let text = adminStr(cfg, key);
  for (const [index, value] of Object.entries(args)) {
    text = text.replace(`%${index}$s`, String(value));
  }
  return text;
}
