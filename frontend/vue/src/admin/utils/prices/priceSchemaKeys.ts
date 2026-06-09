export function slugPriceKey(label: string, prefix: string): string {
  const base = label
    .toLowerCase()
    .normalize('NFD')
    .replace(/\p{M}/gu, '')
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '')
    .slice(0, 24);
  const slug = base || `${prefix}_${Date.now()}`;
  return /^[a-z]/.test(slug) ? slug : `${prefix}_${slug}`;
}

export function uniquePriceKey(
  base: string,
  existing: Record<string, string>,
): string {
  let key = base;
  let n = 2;
  while (key in existing) {
    key = `${base}_${n}`;
    n += 1;
  }
  return key;
}
