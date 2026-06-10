import { describe, expect, it } from 'vitest';
import {
  formatPriceZoneLabel,
  formatPriceZoneList,
  formatPriceZoneSpan,
} from '../src/shared/priceZoneLabels';

describe('priceZoneLabels', () => {
  it('maps zones 1–4 to A–D', () => {
    expect(formatPriceZoneLabel(1)).toBe('A');
    expect(formatPriceZoneLabel(2)).toBe('B');
    expect(formatPriceZoneLabel(3)).toBe('C');
    expect(formatPriceZoneLabel(4)).toBe('D');
  });

  it('falls back to numeric label beyond D', () => {
    expect(formatPriceZoneLabel(5)).toBe('5');
    expect(formatPriceZoneLabel(12)).toBe('12');
  });

  it('formats zone lists and spans', () => {
    expect(formatPriceZoneList([1, 3])).toBe('A, C');
    expect(formatPriceZoneList([])).toBe('—');
    expect(formatPriceZoneSpan(1)).toBe('zon A');
    expect(formatPriceZoneSpan(3)).toBe('zon A–C');
    expect(formatPriceZoneSpan(6)).toBe('6 zoner');
  });
});
